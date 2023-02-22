var arrServiciosVuelo=["CO","PM","PR","CS","VS"];
var arrServiciosTierra=["CR","SR","SIM","RM","OC","DI"];
var arrServiciosLibre=["LI","LN","VA","FR","VA","BA","RT","LB"];
var arrServiciosImaginaria=["IM"];
var arrServiciosSA=["SA"];

var rutaApi="/mychoavenomina/api/programacion"

var importePerfil=0;
var horasPerfil="";
var resumenPerfil="";

var importePerfil_sentencia=0;
var horasPerfil_sentencia="";
var resumenPerfil_sentencia="";

var contadorIms=0;
var importeIms=0;

var contadorVS=0;
var importeVS=0;

var horasActividad="";
var horasActividadNocturna="";
var importeNoc=0;

var horasActividadEx="";
var importeEx=0;

var desglosePerfil="";
var desglosePerfil_sentencia="";

var numeroDietas=0;

var numeroDNC=0;
var numeroDNP=0;
var numeroDNT=0;

var numeroDIC=0;
var numeroDIP=0;
var numeroDIT=0;

var numeroDLC=0;
var numeroDLC2=0;
var numeroDLP=0;
var numeroDLP2=0;
var numeroDLT=0;
var numeroDLT2=0;

var dietasExentas=0;
var dietasSujetas=0;
var dietasBruto=0;

var mesINFORME=0;
var textoAlmacenado="";


window.onload=function(){

    document.getElementById("nivel").value="N2P2";

    document.getElementById("formConfigurador").addEventListener("submit",enviarForm);

    document.getElementById("porcentaje_reduccion").addEventListener("change",clickPorcentaje);
    document.getElementById("porcentaje_reduccion").addEventListener("keyup",clickPorcentaje);
    document.getElementById("porcentaje_reduccion").addEventListener("click",clickPorcentaje);

    document.getElementById("dias_cobro").addEventListener("change",clickDias);
    document.getElementById("dias_cobro").addEventListener("keyup",clickDias);
    document.getElementById("dias_cobro").addEventListener("click",clickDias);

    document.getElementById("flota").addEventListener("change",clickFlota);

    document.getElementById("btnMemoProg").addEventListener("click",memorizarHoras);

    document.getElementById("linkCopiar").addEventListener("click",copiaProgAnterior);

    //UIkit.modal(document.getElementById("advertencia")).show();


}

function copiaProgAnterior(){

    document.getElementById("progra").value="";
    document.getElementById("progra").value=textoAlmacenado;

}

function memorizarHoras(){

    let prograMemorizar=procesaProg(document.getElementById("progra").value);

    let datosMemorizar={};

    datosMemorizar.prograMEM=prograMemorizar;

    //console.log(datosMemorizar.prograMEM);

    doPost(datosMemorizar);

}



function clickFlota(eve){

    if(eve.target.value=="B737"){

        document.getElementById("tiempo_firma").value=60;

    }else{

        document.getElementById("tiempo_firma").value=90;

    }

}

function clickDias(eve){

    let valor= eve.target.value;
    document.getElementById("porcentaje_reduccion").value=(valor*100/30).toFixed(2);

}

function clickPorcentaje(eve){

    let valor= eve.target.value;
    document.getElementById("dias_cobro").value=(valor/100*30).toFixed(2);

}

function volverPreparacion(eve){

    importePerfil=0;
    horasPerfil="";
    resumenPerfil="";

    importePerfil_sentencia=0;
    horasPerfil_sentencia="";
    resumenPerfil_sentencia="";

    contadorIms=0;
    importeIms=0;

    contadorVS=0;
    importeVS=0;

    horasActividad="";
    horasActividadNocturna="";
    importeNoc=0;

    horasActividadEx=0;
    importeEx=0;

    numeroDietas=0;

    numeroDNC=0;
    numeroDNP=0;
    numeroDNT=0;

    numeroDIC=0;
    numeroDIP=0;
    numeroDIT=0;

    numeroDLC=0;
    numeroDLC2=0;
    numeroDLP=0;
    numeroDLP2=0;
    numeroDLT=0;
    numeroDLT2=0;

    dietasExentas=0;
    dietasSujetas=0;
    dietasBruto=0;

    //****************aqui hay qua vaciar el contenido del div para que no salga repetida */
    let divResultados=document.getElementById("divResultados");

    while(divResultados.firstChild){

        divResultados.removeChild(divResultados.firstChild);

    }

    document.getElementById("divPreparacion").style.display="block";
    document.getElementById("divResultados").style.display="none";

    document.getElementById("divSeccionEsconder").style.display="block";

    document.getElementById("tituloInicial").innerHTML="Preparacion:";

    document.getElementById("btnMemoProg").style.display="block";
    document.getElementById("lblProg").innerHTML="Copia aqui Programacion Publicada (Horas Block PROGRAMADAS)";
    document.getElementById("progra").value="";
    document.getElementById("btnEnviar").style.display="none";
    document.getElementById("linkCopiar").style.display="none";

    document.getElementById("pResumen").style.display="none";

    copiaProgAnterior();

    console.clear();

}

function enviarForm(eve){

    eve.preventDefault();

    let piloto=procesaPiloto();

    let progra=procesaProg(document.getElementById("progra").value);

    let datos={};
    datos.piloto=piloto;
    datos.progra=progra;

    doPost(datos);

}

function procesaPiloto(){

    let valor=false;
    if(document.getElementById("tablas_antiguas").checked==true){
        valor=true;
    }else{valor=false};

    let valor2=false;
    if(document.getElementById("tablas_sentencia").checked==true){
        valor2=true;
    }else{valor2=false};

    const piloto={
        nivel: document.getElementById("nivel").value,
        dietas: document.getElementById("dietas").value,
        base: document.getElementById("base").value,
        flota: document.getElementById("flota").value,
        dias_cobro: document.getElementById("dias_cobro").value,
        porcentaje_reduccion :document.getElementById("porcentaje_reduccion").value,
        tiempo_firma :document.getElementById("tiempo_firma").value,
        tiempo_desfirma :document.getElementById("tiempo_desfirma").value,
        aclimatado :document.getElementById("aclimatado").value,
        tablas_antiguas: valor,
        tablas_sentencia: valor2,


    };

    return piloto;

}


function procesaProg(cadenaProg){

 let arrProg=new Array();
 let programacion=[];

 //quito los tabuladores y los subsituyo por espacios
 let cadenaProg2=cadenaProg.replace(/\t+/g, ' ');
 //quito los caracteres que aaparecen en VA, CR, etc
 let cadenaProg3=cadenaProg2.replace(/ - /g, '');
 //quito los saltos de linea del final del archivo
 let cadenaProg4=cadenaProg3.replace(/\n+$/, '')

 //separo en un array por lineas
 let lineasProg= cadenaProg4.split("\n");

 //quito caracteres malos al final del archivo
 let lineasProgFiltradas=lineasProg.filter(function(valor,index,arr){

    if(valor=="" || valor=="\n" || valor=="\t" || valor==" "){
            return false;
        }else{
            return true;
        }

 });

 //separo por espacios y quito campos en blanco y campos (LT)
 let x=0;

 for(linea of lineasProgFiltradas){

    let campos=linea.split(" ");

    let camposFiltrados=campos.filter(function(valor, index, arr){

        if(valor=="" || valor=="(LT)" ||
        valor==" " || valor=="\t"){
            return false;
        }else{
            return true;
        }

    });

    arrProg[x]=camposFiltrados;

    x++;

 }

 //finalmente procesaremos todos los datos y juntaremos campos fecha y hora
 let observaciones="";

 for (linea of arrProg){


    observaciones=linea[0].slice(2,linea[0].length);

    //pillamos los dos primeros caractres
    linea[0]=linea[0].slice(0,2);


    if(linea[0]=="SA" || linea[0]=="RT"){

        linea[1]=linea[1] + " 00:00";
        linea[3]=linea[3] + " 00:00";

    }else{

        linea[1]=linea[1] + " " + linea[2];
        linea.splice(2,1);
        linea[3]=linea[3] + " " + linea[4];
        linea.splice(4,1);

    }


    contador=5;

    while(contador<linea.length){
        observaciones=observaciones + linea[contador] + " ";
        contador++;
    }

    //meto en la ultima posicion las observaciones

    linea[5]=observaciones.trim();
    //me cargo el resto de elementos sobrantes
    linea.length=6;

    //POR ULTIMO CREAMOS UN OBJECTO PROGRAMACION

    let servicio={
        tipo: linea[0],
        fechaIni: linea[1],
        aptIni: linea[2],
        fechaFin: linea[3],
        aptFin: linea[4],
        misc: linea[5],
    };

    programacion.push(servicio);

 }

 return programacion;

}

/**
 * FUNCION QUE MANDA LOS DATOS AL BACKEND PARA SER PROCESADOS
 * @param {
 * } $datos objeto datos que incluye la info del piloto y la programacion ya parseada
 */
function doPost(datos){

    let req= new XMLHttpRequest();

       req.open("POST", rutaApi + '?random=' + Math.floor(Math.random() * 10001));

       req.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0");

       req.setRequestHeader('Content-Type', "application/json");

       req.setRequestHeader('Accept', "application/json");

       req.send(JSON.stringify(datos));
       //debug
       console.log("**************** DATOS ENVIADOS FINAL **********************");
       console.log(JSON.stringify(datos));

       req.addEventListener("readystatechange",listenPost);

}

/**
 * FUNCION QUE SE EJECUTA AL LLEGAR LA PROGRAMACION YA PROCESADA POR EL SERVIDOR
 * @param {*} evento del listener
 */
function listenPost(eve){

    //Si ha finalizado la peticion
    if (eve.target.readyState == 4) {
        //Si el estado es OK
        if (eve.target.status == 200) {
            //debug Recupero los datos devueltos
            console.log("*********** DATOS RECIBIDOS: ***************");

            console.log(eve.target.responseText);

            presentaResultados(eve.target.responseText);

            //error cabecera distinta de 200
            } else {
            //muestro codigo de error
            const msg = JSON.parse(eve.target.responseText);

            console.log("ERROR ************");

            console.log(msg.error);

        }
    }
}

function crearResumen(){

    //resumen imaginarias
    const pImaginarias= document.createElement("p");
    let cadena= contadorIms + " Imaginarias, " + importeIms.toFixed(2) + " €";
    const txtImaginarias=document.createTextNode(cadena);

    pImaginarias.appendChild(txtImaginarias);

    //resumen VS
    const pVS= document.createElement("p");
    let cadenaVS= contadorVS + " Vuelos Situacion, " + importeVS.toFixed(2) + " €";
    const txtVS=document.createTextNode(cadenaVS);

    pVS.appendChild(txtVS);

    //resumen perfil
    const pPerfil= document.createElement("p");
    let cadena2= horasPerfil + " Horas Perfil, " + importePerfil.toFixed(2) + " €, (" + desglosePerfil + ")";
    const txtPerfil=document.createTextNode(cadena2);

    pPerfil.appendChild(txtPerfil);

    //resumen perfil sentencia
    const pPerfil_sentencia= document.createElement("p");
    if(horasPerfil_sentencia!=""){

        let cadena22= horasPerfil_sentencia + " Horas Perfil Sentencia, " + importePerfil_sentencia.toFixed(2) + " €, (" + desglosePerfil_sentencia + ")";
        const txtPerfil_sentencia=document.createTextNode(cadena22);

        pPerfil_sentencia.appendChild(txtPerfil_sentencia);

    }

    //resumen actividad
    const pActividad= document.createElement("p");
    let cadena3= "Total Actividad: " + horasActividad + ", Nocturna: " +
    horasActividadNocturna + " (" + importeNoc + "€)" + ", Extraordinaria: " +
    horasActividadEx + " (" + importeEx + "€)";
    const txtActividad=document.createTextNode(cadena3);

    pActividad.appendChild(txtActividad);

    //resumen dietas
    const pDietas= document.createElement("p");
    let cadena4= numeroDietas + " Dietas, Total BRUTO: " + dietasBruto.toFixed(2) +
    "€, Exentas Tributacion: " + dietasExentas.toFixed(2) +
    "€, Sujetas Retencion: " + dietasSujetas.toFixed(2) + "€.";

    const pDietasDesglose= document.createElement("p");
    let cadena5= "Dietas por tipo:";

    if (numeroDNC!=0) cadena5 = cadena5 + " (DNC = " + numeroDNC + ")" ;
    if (numeroDNP!=0) cadena5 = cadena5 + " (DNP = " + numeroDNP + ")" ;
    if (numeroDNT!=0) cadena5 = cadena5 + " (DNT = " + numeroDNT + ")" ;

    if (numeroDIC!=0) cadena5 = cadena5 + " (DIC = " + numeroDIC + ")" ;
    if (numeroDIP!=0) cadena5 = cadena5 + " (DIP = " + numeroDIP + ")" ;
    if (numeroDIT!=0) cadena5 = cadena5 + " (DIT = " + numeroDIT + ")" ;

    if (numeroDLC!=0) cadena5 = cadena5 + " (DLC = " + numeroDLC + ")" ;
    if (numeroDLC2!=0) cadena5 = cadena5 + " (DLC2 = " + numeroDLC2 + ")" ;
    if (numeroDLP!=0) cadena5 = cadena5 + " (DLP = " + numeroDLP + ")" ;
    if (numeroDLP2!=0) cadena5 = cadena5 + " (DLP2 = " + numeroDLP2 + ")" ;
    if (numeroDLT!=0) cadena5 = cadena5 + " (DLT = " + numeroDLT + ")" ;
    if (numeroDLT2!=0) cadena5 = cadena5 + " (DLT2 = " + numeroDLT2 + ")" ;





    const txtDietas=document.createTextNode(cadena4);
    const txtDietas2=document.createTextNode(cadena5);

    pDietas.appendChild(txtDietas);
    pDietasDesglose.appendChild(txtDietas2);




    const divResumen=document.createElement("div");
    divResumen.classList.add("uk-card");
    divResumen.classList.add("uk-card-body");
    divResumen.classList.add("uk-card-primary");
    divResumen.classList.add("uk-inveres");

    if (mesINFORME==0) mesINFORME="No Especificado";

    divResumen.innerHTML='<h3 class="uk-card-title">Resumen mes: ' + mesINFORME + '</h3>';
    divResumen.appendChild(pImaginarias);
    divResumen.appendChild(pVS);
    divResumen.appendChild(pPerfil);
    //resumen perfil sentencia
    if(horasPerfil_sentencia!="") divResumen.appendChild(pPerfil_sentencia);
    divResumen.appendChild(pActividad);
    divResumen.appendChild(pDietas);
    divResumen.appendChild(pDietasDesglose);
    document.getElementById("divResultados").appendChild(divResumen);





    //BOTON VOLVER:
    const btnVolver= document.createElement("button");
    btnVolver.classList.add("boton");
    const txtBtnVolver=document.createTextNode("Volver!");
    btnVolver.appendChild(txtBtnVolver);
    btnVolver.addEventListener("click",volverPreparacion);
    document.getElementById("divResultados").appendChild(btnVolver);

}

function listoParaInforme(PROGRA){

    document.getElementById("pResumen").innerHTML="";
    //ocultar elementos innecesarios:

    let cadena="<p>VUELOS MEMORIZADOS: " + PROGRA.numVuelosMemorizados + "</p><br>";

    for (let linea of PROGRA.vuelos){

        cadena="<p>" + cadena + linea.aptIni  + linea.aptFin + " " +
        linea.fechaIniProg.date.slice(0,-10) + "</p>";

    }


    document.getElementById("pResumen").innerHTML="<strong>" + cadena + "<br></strong>";
    document.getElementById("pResumen").style.color="darkgreen";
    document.getElementById("pResumen").style.display="block";


    document.getElementById("divSeccionEsconder").style.display="none";

    document.getElementById("tituloInicial").innerHTML="Finalizacion:";

    document.getElementById("btnMemoProg").style.display="none";
    document.getElementById("lblProg").innerHTML="Copia aqui Programacion <strong>VOLADA</strong> (Horas Block Reales)";
    textoAlmacenado=document.getElementById("progra").value;
    document.getElementById("progra").value="";
    document.getElementById("btnEnviar").style.display="block";
    document.getElementById("linkCopiar").style.display="inline";

}

function presentaResultados(unArchivoJson){

    let PROGRA=JSON.parse(unArchivoJson);

    if(PROGRA.tipo=="ERROR"){

        console.log(PROGRA);
        escribeError(PROGRA);
        document.getElementById("divPreparacion").style.display="none";
        document.getElementById("divResultados").style.display="block";
        return;

    }else if(PROGRA.tipo=="MEM"){

        //alert (PROGRA.numVuelosMemorizados + " Vuelos programados Memorizados.");

        listoParaInforme(PROGRA);

        return;

    }

    document.getElementById("divPreparacion").style.display="none";

    document.getElementById("divResultados").style.display="block";

    for(let LINEA of PROGRA){

        let codigoServicio=LINEA.tipo.substr(0,2);

        if (arrServiciosVuelo.includes(codigoServicio)){

            escribeVuelo(LINEA);

        }else if (arrServiciosImaginaria.includes(codigoServicio)){

            escribeImaginaria(LINEA);

        }else if (arrServiciosTierra.includes(codigoServicio)){

            escribeTierra2(LINEA);

        }else if (arrServiciosLibre.includes(codigoServicio)){

            escribeLibre(LINEA);

        }else if (arrServiciosSA.includes(codigoServicio)){

            if(LINEA.arrDietas==undefined){

                escribeLibre(LINEA);

            }else{

                escribeSA(LINEA);

            }

        }else{

            escribeAlgoRaro(LINEA);
        }

    }

    crearResumen();

}

function escribeError(objError){

    unDiv=document.createElement("div");
    unDiv.classList.add("error");
    unDiv.innerHTML="<h4>" + objError.tipo + "<p>" +
    " la siguinete linea ha ocasionado un error:</p><p>" +
    objError.aptIni + " " + objError.aptIni +  "</p>" +
    "<p>" + objError.fechaIni + " " + objError.fechaFin + "</p><p>" +
    objError.mensaje +
    "</p></h4>";

    document.getElementById("divResultados").appendChild(unDiv);

    importePerfil=0;
    horasPerfil="";
    resumenPerfil="";

    importePerfil_sentencia=0;
    horasPerfil_sentencia="";
    resumenPerfil_sentencia="";

    contadorIms=0;
    importeIms=0;

    crearResumen();

}


function escribeVuelo(linea){

    let unContenedor=escribeContenedorServicio(linea);

    let i=0;

    for (let vuelo of linea.arrVuelos){

        let unDiv=document.createElement("div");
        unDiv.classList.add("servicioVuelo");

        if(linea.arrVuelos[i].fantasma==true) unDiv.classList.add("fantasma");

        unDiv.innerHTML=
        '<ul uk-accordion>' +
                '<li>' +
                '<a class="uk-accordion-title" href="#">' +
                '<h3>' + linea.arrVuelos[i].tipo + " " +
                    linea.arrVuelos[i].aptIni + linea.arrVuelos[i].aptFin + ' (' +
                    linea.arrVuelos[i].misc +
                    ')</h3>' +
                '</a>' +
            '<div class="uk-accordion-content">' +
                '<p>Perfil: id(' +
                linea.arrVuelos[i].perfil.id + ') ' + linea.arrVuelos[i].perfil.tipo + ' ' +
                linea.arrVuelos[i].perfil.codigo_completo + ' ' + linea.arrVuelos[i].perfil.codigo_flota +
                '</p>' +
            '</div>' +
                '</li>' +
            '</ul>';

        let unH4= document.createElement("h4");

        unH4.innerHTML= '<p>BlockOff: ' +
        convertirFechaHora(linea.arrVuelos[i].fechaIni.date.substr(0,16)) + '<span class="uk-text-danger">Z</span></p><p>BlockOn: ' +
        convertirFechaHora(linea.arrVuelos[i].fechaFin.date.substr(0,16)) +
        '<span class="uk-text-danger">Z</span></p><p>Horas Block: ' + convertirCadenaHsMs(linea.arrVuelos[i].tiempoBlock.h, linea.arrVuelos[i].tiempoBlock.i) +
        ', AcumuladoBlock: ' + convertirCadenaHsMs(linea.arrVuelos[i].contadorHblock, linea.arrVuelos[i].contadorMblock) +
        '</p>';

        //si el perfil es diferente de false es pq existe en bbdd
        if(linea.arrVuelos[i].perfil!=false){

            unH4.innerHTML=unH4.innerHTML +
            '<p>Horas Perfil: ' + linea.arrVuelos[i].perfil.tiempo_perfil.substr(0,5) +
            ', AcumuladoPerfil: ' + convertirCadenaHsMs(linea.arrVuelos[i].contadorHperfil, linea.arrVuelos[i].contadorMperfil) +
            '</p>';


        }else{

            unH4.innerHTML=unH4.innerHTML + '<p class="uk-text-warning">Perfil No Encontrado en BBDD, no se va a contabilizar. </p>';

        }


        unH4.innerHTML=unH4.innerHTML +
        '<p>Perfil: ' +
        linea.arrVuelos[i].importePorEsteVuelo.toFixed(2) + '€, Accu ' +
        linea.arrVuelos[i].importePerfil.toFixed(2) +
        '€</p><p>Desglose Perfil: ' + linea.arrVuelos[i].observaciones +
        '</p>';

        desglosePerfil=linea.arrVuelos[i].observaciones;
        desglosePerfil_sentencia=linea.arrVuelos[i].observaciones_sentencia;

        unDiv.appendChild(unH4);
        unContenedor.appendChild(unDiv);

        //actualizo las ariables globales para el reumen final (hay que hacerlo en cada vuelo)
        horasPerfil=convertirCadenaHsMs(linea.arrVuelos[i].contadorHperfil, linea.arrVuelos[i].contadorMperfil);
        importePerfil=linea.arrVuelos[i].importePerfil;
        resumenPerfil=linea.arrVuelos[i].observaciones;

        //***actualizo las variables globales sentencia para el reumen final (hay que hacerlo en cada vuelo)
        if(linea.piloto.sentenciaPerfiles==true){

            horasPerfil_sentencia=convertirCadenaHsMs(linea.arrVuelos[i].contadorHperfil_sentencia, linea.arrVuelos[i].contadorMperfil_sentencia);
            importePerfil_sentencia=linea.arrVuelos[i].importePerfil_Sentencia;
            resumenPerfil_sentencia=linea.arrVuelos[i].observaciones_sentencia;

        }


        //variables globales para vs
        if(linea.arrVuelos[i].tipo=="VS"){
            //actualizo las ariables globales para el reumen final (hay que hacerlo en cada vuelo)
            horasVS=linea.arrVuelos[i].perfil.tiempo_perfil.substr(0,5);
            importeVS= importeVS + linea.arrVuelos[i].importePerfil;
            contadorVS=contadorVS+1;
            resumenPerfil=linea.arrVuelos[i].observaciones;

        }


        //augmento el contador para el siguinete vuelo
        i++;


    }


    document.getElementById("divResultados").appendChild(unContenedor);

}

function escribeImaginaria(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("servicioTierra");
    unDiv.innerHTML="<h3>" + linea.tipo + "<p>" +
    convertirFechaHora(linea.fechaIni.date.substr(0,16)) + "</p><p>" +
    convertirFechaHora(linea.fechaFin.date.substr(0,16)) + "</p>" +
    '<p> (Horas IM: ' + convertirCadenaHsMs(linea.tiempoImaginaria.h, linea.tiempoImaginaria.i) +
    ') Importe IM: ' + linea.importeImaginaria + "€, Sumatorio IMs: " + linea.contadorImporteImaginarias +
    "€, equivalen a " + linea.contadorNumImaginarias + " IM(s)" +
    '</p><p>Se han añadido 12h a la actividad acumulada.</p></h3>';

    document.getElementById("divResultados").appendChild(unDiv);

    //actualizo las variables globales para el resumen final
    contadorIms = linea.contadorNumImaginarias;
    importeIms = linea.contadorImporteImaginarias;

}

function escribeTierra(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("servicioTierra");
    unDiv.innerHTML="<h4>" + linea.tipo + " " + linea.aptFin + "<p>" +
    convertirFechaHora(linea.fechaIni.date.substr(0,16)) + "</p><p>" +
    convertirFechaHora(linea.fechaFin.date.substr(0,16)) + "</p>" +
    '<p> Horas Actividad: ' + convertirCadenaHsMs(linea.tiempoActividad.h, linea.tiempoActividad.i) +
    ', Accu: ' + convertirCadenaHsMs(linea.contadorHact, linea.contadorMact) +
    "</p></h4>";


    if(linea.arrDietas!=null){

        let unDivDieta=escribeDieta(linea);

        unDiv.appendChild(unDivDieta);

    }


    document.getElementById("divResultados").appendChild(unDiv);

}

function escribeTierra2(linea){

    let unContenedor=escribeContenedorServicio(linea);

    let i=0;

    for (let suelo of linea.arrSuelos){

        let unDiv=document.createElement("div");
        unDiv.classList.add("servicioTierra");

        if(linea.arrSuelos[i].fantasma==true) unDiv.classList.add("fantasma");

        unDiv.innerHTML=
        '<ul uk-accordion>' +
                '<li>' +
                '<a class="uk-accordion-title" href="#">' +
                '<h3>' + linea.arrSuelos[i].tipo + " " +
                    linea.arrSuelos[i].aptIni + linea.arrSuelos[i].aptFin + ' (' +
                    linea.arrSuelos[i].misc +
                    ')</h3>' +
                '</a>' +
            '<div class="uk-accordion-content">' +
                '<p>Info ' +
                linea.arrSuelos[i].misc +
                '</p>' +
            '</div>' +
                '</li>' +
            '</ul>';

        let unH4= document.createElement("h4");

        unH4.innerHTML= '<p>Inicio: ' +
        convertirFechaHora(linea.arrSuelos[i].fechaIni.date.substr(0,16)) + '<span class="uk-text-danger">Z</span></p><p>Final: ' +
        convertirFechaHora(linea.arrSuelos[i].fechaFin.date.substr(0,16)) +
        '<span class="uk-text-danger">Z</span></p><p>Transcurrido: ' + convertirCadenaHsMs(linea.arrSuelos[i].tiempoBlock.h, linea.arrSuelos[i].tiempoBlock.i) +
        '</p>';

        unDiv.appendChild(unH4);
        unContenedor.appendChild(unDiv);

        //augmento el contador para el siguinete vuelo
        i++;

    }


    document.getElementById("divResultados").appendChild(unContenedor);

}

function escribeSA(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("servicioSA");

    unDiv.innerHTML=
    '<ul uk-accordion>' +
    '<li>' +
        '<a class="uk-accordion-title" href="#">' +
            '<h3><span class="uk-label">' + dameDia(linea.arrDietas[0].diaDieta.date) + '</span> SA '  +
             linea.aptFin + '</h3>' +
        '</a>' +
        '<div class="uk-accordion-content">' + linea.misc + '</div>' +
    '</li>' +
    '</ul>' ;

    let unDivDieta=escribeDieta(linea);

    unDiv.appendChild(unDivDieta);

    document.getElementById("divResultados").appendChild(unDiv);


}

/**
 *
 * @param {linea} linea de la programacion que se procesoa
 * devuelve un div que contiene la dieta aparte de augmentar los contadores para el resumen
 *
 */
function escribeDieta(linea){

   const mesInforme=linea.mesDelInforme;

    mesINFORME=mesInforme;

    let ddieta=linea.arrDietas[0].diaDieta.date;

    const diaDieta=new Date(ddieta.replace(" ","T"));

    const mesDieta=diaDieta.getMonth() + 1;

    let i=0;

    if(mesDieta!=mesInforme && mesInforme!=0){

        let observaciones= "Esta Dieta se percibe en otro mes.";

        let unDivDieta=document.createElement("div");

        unDivDieta.classList.add("dieta");
        unDivDieta.classList.add("fantasma");

        unDivDieta.innerHTML=
            '<ul uk-accordion>' +
                    '<li>' +
                    '<a class="uk-accordion-title" href="#">' +
                    '<h3>' + linea.arrDietas[i].codigo + " " +
                        linea.arrDietas[i].arrDatosDieta.nombre + '</h3>' +
                    '</a>' +
                '<div class="uk-accordion-content">' +
                    '<p>'  + "Bruto: " + linea.arrDietas[i].arrDatosDieta.bruto + '€, Exento: '+ linea.arrDietas[i].arrDatosDieta.exento + '€' +
                    '</p>' +
                    '<p>'  + linea.arrDietas[i].misc + observaciones +
                    '</p>' +
                '</div>' +
                    '</li>' +
                '</ul>';

        return unDivDieta;

    }


    let observaciones= "mes Informe: " + mesInforme + ", mes Dieta: " + mesDieta;

    i=0;

    let unDivDieta=document.createElement("div");

    unDivDieta.classList.add("dieta");

    unDivDieta.innerHTML=
        '<ul uk-accordion>' +
                '<li>' +
                '<a class="uk-accordion-title" href="#">' +
                '<h3>' + linea.arrDietas[i].codigo + " " +
                    linea.arrDietas[i].arrDatosDieta.nombre + '</h3>' +
                '</a>' +
            '<div class="uk-accordion-content">' +
                '<p>'  + "Bruto: " + linea.arrDietas[i].arrDatosDieta.bruto + '€, Exento: '+ linea.arrDietas[i].arrDatosDieta.exento + '€' +
                '</p>' +
                '<p>'  + linea.arrDietas[i].misc + observaciones +
                '</p>' +
            '</div>' +
                '</li>' +
            '</ul>';

    dietasBruto=dietasBruto + parseFloat(linea.arrDietas[i].arrDatosDieta.bruto);
    dietasExentas=dietasExentas + parseFloat(linea.arrDietas[i].arrDatosDieta.exento);
    dietasSujetas=dietasSujetas + parseFloat((linea.arrDietas[i].arrDatosDieta.bruto - linea.arrDietas[i].arrDatosDieta.exento));

    sumaParcialesDieta(linea.arrDietas[i].codigo);

    numeroDietas++;

    return unDivDieta;

}

function sumaParcialesDieta(cod_dieta){

    switch (cod_dieta) {
        case 'DNC':
          numeroDNC++;
          break;
        case 'DNP':
            numeroDNP++;
            break;
        case 'DNT':
          numeroDNT++;
          break;
        case 'DIC':
            numeroDIC++;
            break;
        case 'DIP':
          numeroDIP++;
          break;
        case 'DIT':
            numeroDIT++;
            break;
        case 'DLC':
          numeroDLC++;
          break;
        case 'DLP':
            numeroDLP++;
            break;
        case 'DLT':
          numeroDLT++;
          break;
        case 'DLC_2 (redu 3/4)':
          numeroDLC2++;
          break;
        case 'DLP_2 (redu 3/4)':
            numeroDLP2++;
            break;
        case 'DLT_2 (redu 3/4)':
          numeroDLT2++;
          break;

        default:
          console.log("Esta dieta no existe " + cod_dieta);
      }



}

function escribeLibre(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("servicioLibre");
    unDiv.innerHTML="<h3>"+linea.tipo+" "+
    convertirFechaHora(linea.fechaIni.date.substr(0,16)) + ' hasta: ' +
    convertirFechaHora(linea.fechaFin.date.substr(0,16)) +
    "</h3>";

    document.getElementById("divResultados").appendChild(unDiv);


}

function escribeAlgoRaro(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("servicioError");
    unDiv.innerHTML="<p>"+linea.tipo+" "+linea.aptIni+linea.aptFin+"</p>";

    // if (linea.tipo=="CO") consultaPerfil(linea.aptIni+linea.aptFin);

    document.getElementById("divResultados").appendChild(unDiv);


}

function escribeContenedorServicio(linea){

    unDiv=document.createElement("div");
    unDiv.classList.add("contenedorServicios");

    if(linea.fantasma==true) unDiv.classList.add("fantasma");

    unDiv.innerHTML=
    '<ul uk-accordion>' +
    '<li>' +
        '<a class="uk-accordion-title" href="#">' +
            '<h3><span class="uk-label">' + dameDia(linea.fechaFirma.date) + '</span> Servicio '  +
            linea.aptIni + ' - ' + linea.aptFin + '</h3>' +
        '</a>' +
        '<div class="uk-accordion-content">' + linea.misc + '</div>' +
    '</li>' +
    '</ul>' ;


    unDiv.innerHTML=unDiv.innerHTML+'<h4><p>' +
    'Presentacion: ' + convertirFechaHora(linea.fechaFirma.date.substr(0,16)) + '<span class="uk-text-danger">Z</span></p><p>' +
    'Fin Actividad: ' + convertirFechaHora(linea.fechaDesfirma.date.substr(0,16)) + '<span class="uk-text-danger">Z</span></p></h4>';

    unDiv.innerHTML=unDiv.innerHTML +

    '<h4><table class="tabla_act"><tr><th>ACTIVIDAD</th><th>ACTUAL</th><th>ACUMULADO</th></tr><tr><th>TOTAL</th>' +
      '<td>' + convertirCadenaHsMs(linea.tiempoActividad.h, linea.tiempoActividad.i) + '</td>' +
      '<td>' + convertirCadenaHsMs(linea.contadorHact, linea.contadorMact) + '</td>' +
    '</tr><tr><th>NOCTURNA</th>' +
      '<td>' + convertirCadenaHsMs(linea.tiempoActividadNocturna.h, linea.tiempoActividadNocturna.i) +
      ' (' + parseFloat(linea.importeActividadNoc).toFixed(2) + '€)' + '</td>' +
      '<td>' + convertirCadenaHsMs(linea.contadorHactNoc, linea.contadorMactNoc) +
      ' (' + parseFloat(linea.contadorImpNoc).toFixed(2) + '€)' + '</td>' +
    '</tr><tr><th>EXTRA</th>' +
      '<td>' + convertirCadenaHsMs(linea.tiempoActividadEx.h, linea.tiempoActividadEx.i) +
      ' (' + parseFloat(linea.importeActividadEx).toFixed(2) + '€)' + '</td>' +
      '<td>' + convertirCadenaHsMs(linea.contadorHactEx, linea.contadorMactEx) +
      ' (' + parseFloat(linea.contadorImpEx).toFixed(2) + '€)' + '</td>' +
    '</tr></table></h4>';

    horasActividad=convertirCadenaHsMs(linea.contadorHact, linea.contadorMact);
    horasActividadNocturna=convertirCadenaHsMs(linea.contadorHactNoc, linea.contadorMactNoc);
    importeNoc=parseFloat(linea.contadorImpNoc).toFixed(2);

    horasActividadEx=convertirCadenaHsMs(linea.contadorHactEx, linea.contadorMactEx);
    importeEx=parseFloat(linea.contadorImpEx).toFixed(2);

    //***************ESCRIBIR LAS DIETAS******si las hay************* */
    if(linea.arrDietas!=null){

        let unDivDieta=escribeDieta(linea);

        unDiv.appendChild(unDivDieta);

    }


    return unDiv;

}

function convertirCadenaHsMs(horas,minutos){

    if(horas==undefined || minutos==undefined) return "*err*";

    return horas.toString().padStart(3, '0') + ":" + minutos.toString().padStart(2, '0');

}

function convertirFechaHora(fecha){

    //en ios hay que cambiar al formato estandar exacto para que
    //lo reconozca como fecha
    //https://stackoverflow.com/questions/13363673/javascript-date-is-invalid-on-ios

    let fechaConvertida=new Date(fecha.replace(" ","T"));

    return fechaConvertida.toLocaleString("es-ES").slice(0,-3) ;


}

function dameDia(fecha){

    let fechaConvertida=new Date(fecha.replace(" ","T"));

    return fechaConvertida.getDate();


}