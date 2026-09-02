import pandas as pd
import paramiko
import json
import os

print("1. Leyendo RESPONSABLES.xlsx...")
df = pd.read_excel('RESPONSABLES.xlsx')
df = df.fillna('')

data = []
for index, row in df.iterrows():
    nombre = str(row.get('Nombre', '')).strip()
    correo = str(row.get('Correo', '')).strip()
    especialidad = str(row.get('Especialidad', '')).strip()
    
    if nombre and correo:
        data.append({
            'nombre': nombre,
            'correo': correo,
            'especialidad': especialidad
        })

with open('data.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False)

php_script = """<?php
use Illuminate\\Contracts\\Console\\Kernel;
use App\\Models\\Responsable;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$jsonData = file_get_contents(__DIR__ . '/data.json');
$responsables = json_decode($jsonData, true);

$inserted = 0;
$updated = 0;

foreach ($responsables as $resp) {
    if (empty($resp['correo'])) continue;

    $responsable = Responsable::where('correo', $resp['correo'])->first();
    if ($responsable) {
        $responsable->update([
            'nombre' => $resp['nombre'],
            'especialidad' => $resp['especialidad']
        ]);
        $updated++;
    } else {
        Responsable::create([
            'nombre' => $resp['nombre'],
            'correo' => $resp['correo'],
            'especialidad' => $resp['especialidad']
        ]);
        $inserted++;
    }
}
echo "Importación completada. Insertados: $inserted, Actualizados: $updated\\n";
"""

with open('import_script.php', 'w', encoding='utf-8') as f:
    f.write(php_script)

print(f"Total registros a importar: {len(data)}")
print("2. Conectando al VPS...")
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('167.86.72.200', username='cristian', password='Cristian_5732988$')
print("Conectado con éxito.")

sftp = ssh.open_sftp()
print("3. Subiendo archivos (data.json y import_script.php)...")
sftp.put('data.json', 'apps/sirad/data.json')
sftp.put('import_script.php', 'apps/sirad/import_script.php')
sftp.close()

print("4. Ejecutando la importación en la base de datos...")
stdin, stdout, stderr = ssh.exec_command('cd apps/sirad && php import_script.php')
print("--- Resultado de la importación ---")
print(stdout.read().decode('utf-8'))
err = stderr.read().decode('utf-8')
if err:
    print("Errores:")
    print(err)

print("5. Limpiando archivos temporales...")
ssh.exec_command('rm apps/sirad/data.json apps/sirad/import_script.php')
ssh.close()
os.remove('data.json')
os.remove('import_script.php')

print("Proceso finalizado correctamente.")
