// Este código se encarga de cargar las obras desde un archivo JSON y mostrarlas en la página web.
// Separa las obras civiles e industriales y crea elementos HTML para cada una de ellas.
// Asegúrate de que el archivo JSON y las imágenes estén en la ruta correcta para que funcione correctamente.

document.addEventListener("DOMContentLoaded", () => {
    fetch("../folders.json")
        .then(response => response.json())
        .then(folders => {
            const contenedor = document.getElementById("contenedorObrasIndustriales");
            contenedor.innerHTML = "";

            Object.entries(folders.ObrasIndustriales).forEach(([folderName, data]) => {
                let obraindex = document.createElement("a");
                obraindex.classList.add("obracivil");
                obraindex.href = "obraindustrial.html?id=" + folderName;
                obraindex.target = "_blank";
                obraindex.style.textDecoration = "none";

                let obraimg = document.createElement("img");
                obraimg.classList.add("obraimg");
                obraimg.src = `../img/obrasindustriales/${folderName}/${folderName}.jpg`;

                let obrainfo = document.createElement("div");

                let obraTitle = document.createElement("h3");
                obraTitle.classList.add("obraTitle");
                obraTitle.textContent = data.Titulo;

                let obraUbi = document.createElement("span");
                obraUbi.classList.add("obraUbi");
                obraUbi.textContent = data.Ubicacion;

                obrainfo.appendChild(obraUbi);
                obrainfo.appendChild(obraTitle);
                obraindex.appendChild(obraimg);
                obraindex.appendChild(obrainfo);
                contenedor.appendChild(obraindex);
            });
        })
        .catch(error => console.error("Error al cargar las obras civiles:", error));
});
