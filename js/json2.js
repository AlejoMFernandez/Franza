const fs = require('fs');
const path = require('path');

const ObrasCivilesPath = path.join(__dirname, '../img/obrasciviles');
const ObrasIndustrialesPath = path.join(__dirname, '../img/obrasindustriales');

const obras = {
  ObrasCiviles: {},
  ObrasIndustriales: {}
};

function leerObras(basePath, tipo) {
  fs.readdirSync(basePath, { withFileTypes: true })
    .filter(dirent => dirent.isDirectory())
    .forEach(dir => {
      const carpeta = dir.name;

      const txtPath = path.join(basePath, carpeta, `${carpeta}.txt`);

      if (fs.existsSync(txtPath)) {
        const contenido = fs.readFileSync(txtPath, 'utf-8').split('\n');
        const datos = {};

        contenido.forEach(linea => {
          const [clave, ...valorArr] = linea.split(':');
          if (clave && valorArr.length > 0) {
            datos[clave.trim()] = valorArr.join(':').trim();
          }
        });

        obras[tipo][carpeta] = datos;
      } else {
        console.warn(`⚠️  No se encontró el archivo ${carpeta}.txt en ${tipo}`);
      }
    });
}

// Leer cada tipo de obra por separado
leerObras(ObrasCivilesPath, 'ObrasCiviles');
leerObras(ObrasIndustrialesPath, 'ObrasIndustriales');

// Guardar como JSON final
fs.writeFileSync(path.join(__dirname, '../folders.json'), JSON.stringify(obras, null, 2), 'utf-8');

console.log('✅ Archivo obras.json generado correctamente.');
