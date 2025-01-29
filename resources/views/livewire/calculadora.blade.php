<div class="container">
    <section class="form-register">
        <h4>Calculadora Postal</h4>
        <h3>*Seleciona por que servicio quieres enviar.</h3>
        <select class="controls" name="categoria" id="categoria" wire:model="categoria">
            <option value="">Seleccione un Servicio Postal</option>
            <option class="nacional" value="EMS NAT">EMS Nacional</option>
            <option class="internacional" value="EMS INT">EMS Internacional</option>
            <option class="nacional" value="MI ENCOMIENDA">Mi Encomienda Nacional</option>
            <option class="internacional" value="ENCOMIENDA">Encomienda Internacional</option>
            <option class="nacional" value="LC/AO NAT">LC/AO Nacional</option>
            <option class="internacional" value="LC/AO INT">LC/AO Internacional</option>
            <option class="nacional" value="ECA NAT">ECA Nacional</option>
            <option class="internacional" value="ECA INT">ECA Internacional</option>
            <option class="nacional" value="PLIEGOS NAT">Pliegos Oficiales Nacional</option>
            <option class="internacional" value="PLIEGOS INT">Pliegos Oficiales Internacional</option>
            <option class="nacional" value="SACAS M NAT">Sacas M Nacional</option>
            <option class="internacional" value="SACAS M INT">Sacas M Internacional</option>
            <option class="nacional" value="SUPER NAT">Super Express Nacional</option>
            {{-- <option class="internacional" value="SUPER DOC NAT">Super Express
                Internacional Documentos</option>
            <option class="internacional" value="SUPER PAQUE NAT">Super Express Internacional
                Paquetes</option> --}}
        </select>
        <h3>*Seleciona a donde quieres enviar.</h3>
        <select class="controls" name="destino" id="destino" wire:model="destino">
            <option value="">Seleccione un Destino</option>
            <option value="local_1">Area Urbana (Hasta 2.5 Km)</option>
            <option value="local_2">Area Urbana (Hasta 5 Km)</option>
            <option value="local_3">Area Urbana (Hasta 7.5 Km)</option>
            <option value="local_4">Area Urbana (Hasta 10 Km)</option>
            <option value="dest_a_argentina">Argentina</option>
            <option value="dest_a_brasil">Brasil</option>
            <option value="dest_a_chile">Chile</option>
            <option value="dest_a_colombia">Colombia</option>
            <option value="dest_a_ecuador">Ecuador</option>
            <option value="dest_a_guyana">Guyana</option>
            <option value="dest_a_paraguay">Paraguay</option>
            <option value="dest_a_peru">Perú</option>
            <option value="dest_a_surinam">Surinam</option>
            <option value="dest_a_uruguay">Uruguay</option>
            <option value="dest_a_venezuela">Venezuela</option>
            <option value="dest_b_belice">Belice</option>
            <option value="dest_b_costa_rica">Costa Rica</option>
            <option value="dest_b_el_salvador">El Salvador</option>
            <option value="dest_b_guatemala">Guatemala</option>
            <option value="dest_b_honduras">Honduras</option>
            <option value="dest_b_nicaragua">Nicaragua</option>
            <option value="dest_b_panama">Panamá</option>
            <option value="dest_b_antigua_barbuda">Antigua y Barbuda</option>
            <option value="dest_b_bahamas">Bahamas</option>
            <option value="dest_b_barbados">Barbados</option>
            <option value="dest_b_cuba">Cuba</option>
            <option value="dest_b_dominica">Dominica</option>
            <option value="dest_b_republica_dominicana">República Dominicana</option>
            <option value="dest_b_granada">Granada</option>
            <option value="dest_b_haiti">Haití</option>
            <option value="dest_b_jamaica">Jamaica</option>
            <option value="dest_b_san_cristobal_nieves">San Cristóbal y Nieves</option>
            <option value="dest_b_santa_lucia">Santa Lucía</option>
            <option value="dest_b_san_vicente_granadinas">San Vicente y las Granadinas</option>
            <option value="dest_b_trinidad_tobago">Trinidad y Tobago</option>
            <option value="dest_b_puerto_rico">Puerto Rico</option>
            <option value="dest_c_canada">Canadá</option>
            <option value="dest_c_eeuu">Estados Unidos</option>
            <option value="dest_c_mexico">México</option>
            <option value="dest_d_alemania">Alemania</option>
            <option value="dest_d_andorra">Andorra</option>
            <option value="dest_d_austria">Austria</option>
            <option value="dest_d_belgica">Bélgica</option>
            <option value="dest_d_bielorrusia">Bielorrusia</option>
            <option value="dest_d_bosnia_herzegovina">Bosnia y Herzegovina</option>
            <option value="dest_d_bulgaria">Bulgaria</option>
            <option value="dest_d_chipre">Chipre</option>
            <option value="dest_d_croacia">Croacia</option>
            <option value="dest_d_dinamarca">Dinamarca</option>
            <option value="dest_d_eslovaquia">Eslovaquia</option>
            <option value="dest_d_eslovenia">Eslovenia</option>
            <option value="dest_d_espana">España</option>
            <option value="dest_d_estonia">Estonia</option>
            <option value="dest_d_finlandia">Finlandia</option>
            <option value="dest_d_francia">Francia</option>
            <option value="dest_d_grecia">Grecia</option>
            <option value="dest_d_hungria">Hungría</option>
            <option value="dest_d_irlanda">Irlanda</option>
            <option value="dest_d_islandia">Islandia</option>
            <option value="dest_d_italia">Italia</option>
            <option value="dest_d_kosovo">Kosovo</option>
            <option value="dest_d_letonia">Letonia</option>
            <option value="dest_d_liechtenstein">Liechtenstein</option>
            <option value="dest_d_lituania">Lituania</option>
            <option value="dest_d_luxemburgo">Luxemburgo</option>
            <option value="dest_d_macedonia_norte">Macedonia del Norte</option>
            <option value="dest_d_malta">Malta</option>
            <option value="dest_d_monaco">Mónaco</option>
            <option value="dest_d_montenegro">Montenegro</option>
            <option value="dest_d_noruega">Noruega</option>
            <option value="dest_d_paises_bajos">Países Bajos</option>
            <option value="dest_d_polonia">Polonia</option>
            <option value="dest_d_portugal">Portugal</option>
            <option value="dest_d_reino_unido">Reino Unido</option>
            <option value="dest_d_republica_checa">República Checa</option>
            <option value="dest_d_rumania">Rumania</option>
            <option value="dest_d_rusia">Rusia</option>
            <option value="dest_d_san_marino">San Marino</option>
            <option value="dest_d_serbia">Serbia</option>
            <option value="dest_d_suecia">Suecia</option>
            <option value="dest_d_suiza">Suiza</option>
            <option value="dest_d_ucrania">Ucrania</option>
            <option value="dest_d_vaticano">Vaticano</option>
            <option value="dest_d_arabia_saudita">Arabia Saudita</option>
            <option value="dest_d_barein">Baréin</option>
            <option value="dest_d_catar">Catar</option>
            <option value="dest_d_chipre">Chipre</option>
            <option value="dest_d_emiratos_arabes">Emiratos Árabes Unidos</option>
            <option value="dest_d_irak">Irak</option>
            <option value="dest_d_iran">Irán</option>
            <option value="dest_d_israel">Israel</option>
            <option value="dest_d_jordania">Jordania</option>
            <option value="dest_d_kuwait">Kuwait</option>
            <option value="dest_d_libano">Líbano</option>
            <option value="dest_d_oman">Omán</option>
            <option value="dest_d_palestina">Palestina</option>
            <option value="dest_d_siria">Siria</option>
            <option value="dest_d_turquia">Turquía</option>
            <option value="dest_d_yemen">Yemen</option>
            <option value="dest_e_argelia">Argelia</option>
            <option value="dest_e_angola">Angola</option>
            <option value="dest_e_benin">Benín</option>
            <option value="dest_e_botsuana">Botsuana</option>
            <option value="dest_e_burkina_faso">Burkina Faso</option>
            <option value="dest_e_burundi">Burundi</option>
            <option value="dest_e_cabo_verde">Cabo Verde</option>
            <option value="dest_e_camerun">Camerún</option>
            <option value="dest_e_chad">Chad</option>
            <option value="dest_e_comoras">Comoras</option>
            <option value="dest_e_congo">Congo</option>
            <option value="dest_e_costa_marfil">Costa de Marfil</option>
            <option value="dest_e_djibouti">Djibouti</option>
            <option value="dest_e_egipto">Egipto</option>
            <option value="dest_e_eritrea">Eritrea</option>
            <option value="dest_e_esuatini">Esuatini</option>
            <option value="dest_e_etiopia">Etiopía</option>
            <option value="dest_e_gabon">Gabón</option>
            <option value="dest_e_gambia">Gambia</option>
            <option value="dest_e_ghana">Ghana</option>
            <option value="dest_e_guinea">Guinea</option>
            <option value="dest_e_guinea_bisau">Guinea-Bisáu</option>
            <option value="dest_e_guinea_ecuatorial">Guinea Ecuatorial</option>
            <option value="dest_e_kenia">Kenia</option>
            <option value="dest_e_lesoto">Lesoto</option>
            <option value="dest_e_liberia">Liberia</option>
            <option value="dest_e_libia">Libia</option>
            <option value="dest_e_madagascar">Madagascar</option>
            <option value="dest_e_malawi">Malawi</option>
            <option value="dest_e_mali">Malí</option>
            <option value="dest_e_marruecos">Marruecos</option>
            <option value="dest_e_mauricio">Mauricio</option>
            <option value="dest_e_mauritania">Mauritania</option>
            <option value="dest_e_mozambique">Mozambique</option>
            <option value="dest_e_namibia">Namibia</option>
            <option value="dest_e_niger">Níger</option>
            <option value="dest_e_nigeria">Nigeria</option>
            <option value="dest_e_republica_centroafricana">República Centroafricana</option>
            <option value="dest_e_republica_democratica_congo">República Democrática del Congo</option>
            <option value="dest_e_ruanda">Ruanda</option>
            <option value="dest_e_santo_tome_principe">Santo Tomé y Príncipe</option>
            <option value="dest_e_senegal">Senegal</option>
            <option value="dest_e_seychelles">Seychelles</option>
            <option value="dest_e_sierra_leona">Sierra Leona</option>
            <option value="dest_e_somalia">Somalia</option>
            <option value="dest_e_sudafrica">Sudáfrica</option>
            <option value="dest_e_sudan">Sudán</option>
            <option value="dest_e_sudan_sur">Sudán del Sur</option>
            <option value="dest_e_tanzania">Tanzania</option>
            <option value="dest_e_togo">Togo</option>
            <option value="dest_e_tunez">Túnez</option>
            <option value="dest_e_uganda">Uganda</option>
            <option value="dest_e_zambia">Zambia</option>
            <option value="dest_e_zimbabue">Zimbabue</option>
            <option value="dest_e_australia">Australia</option>
            <option value="dest_e_fiyi">Fiyi</option>
            <option value="dest_e_islas_marshall">Islas Marshall</option>
            <option value="dest_e_islas_salomon">Islas Salomón</option>
            <option value="dest_e_kiribati">Kiribati</option>
            <option value="dest_e_micronesia">Micronesia</option>
            <option value="dest_e_nauru">Nauru</option>
            <option value="dest_e_nueva_zelanda">Nueva Zelanda</option>
            <option value="dest_e_palaos">Palaos</option>
            <option value="dest_e_papua_nueva_guinea">Papúa Nueva Guinea</option>
            <option value="dest_e_samoa">Samoa</option>
            <option value="dest_e_tonga">Tonga</option>
            <option value="dest_e_tuvalu">Tuvalu</option>
            <option value="dest_e_vanuatu">Vanuatu</option>
            <option value="nacional_beni">Beni</option>
            <option value="nacional_chuquisaca">Chuquisaca</option>
            <option value="nacional_cochabamba">Cochabamba</option>
            <option value="nacional_la_paz">La Paz</option>
            <option value="nacional_oruro">Oruro</option>
            <option value="nacional_pando">Pando</option>
            <option value="nacional_potosi">Potosí</option>
            <option value="nacional_santa_cruz">Santa Cruz</option>
            <option value="nacional_tarija">Tarija</option>
            <option value="cui_cap_chuquisaca">Chuquisaca</option>
            <option value="cui_cap_cochabamba">Cochabamba</option>
            <option value="cui_cap_la_paz">La Paz</option>
            <option value="cui_cap_oruro">Oruro</option>
            <option value="cui_cap_pando">Pando</option>
            <option value="cui_cap_potosi">Potosí</option>
            <option value="cui_cap_santa_cruz">Santa Cruz</option>
            <option value="cui_cap_tarija">Tarija</option>
            <option value="cui_int">Cuidades Intermedias</option>
            <option value="cui1">Trinidad / Cobija</option>
            <option value="cui2">Riberalta / Guayaramerin</option>
            <option value="pro_dentro">Provincia Dentro Departamento</option>
            <option value="pro_otro">Provincia en Otro Departamento</option>
        </select>
        <h3>*Ingrese el peso del paquete.(Kg.)</h3>
        <input class="controls" type="number" name="peso" id="peso" wire:model="peso"
            placeholder="Ingrese el Peso Aprox. de su Paquete" step="0.001" min="0">

        <h3>Ingrese las dimensiones del paquete.(OPCIONAL /cm.)</h3>
        <div class="dimension-container">
            <div class="dimension-item">
                <input class="controls" type="number" name="largo" id="largo" wire:model="largo"
                    placeholder="Largo (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <input class="controls" type="number" name="ancho" id="ancho" wire:model="ancho"
                    placeholder="Ancho (cm)" step="0.1" min="0">
            </div>
            <div class="dimension-item">
                <input class="controls" type="number" name="alto" id="alto" wire:model="alto"
                    placeholder="Alto (cm)" step="0.1" min="0">
            </div>
        </div>

        <h3>Selecciona servicios adicionales:</h3>
        <div class="dimension-container">
            <div class="dimension-item">
                <label for="certificado">
                    <input type="checkbox" name="certificado" id="certificado" wire:model="certificado">
                    Paquete Certificado
                </label>
            </div>
            {{-- <div class="dimension-item">
                <label for="espreso">
                    <input type="checkbox" name="espreso" id="espreso" wire:model="espreso">
                    Servicio Expreso
                </label>
            </div> --}}
            <div class="dimension-item">
                <label for="recibo">
                    <input type="checkbox" name="recibo" id="recibo" wire:model="recibo">
                    Aviso de Recibo
                </label>
            </div>
        </div>
        <button class="botons" wire:click="calcular">Calcular</button>
        <p style="font-size: 14px; color: #ffffff;">
            *El precio mostrado es referencial y puede ajustarse según las tarifas vigentes.*
        </p>
        @if ($resultado)
            <div class="resultado">
                <h5>El costo estimado del envío es:</h5>
                <p>{{ $resultado }} Bs.</p>
            </div>
        @endif
    </section>
    
    <div class="icono-mascota">
        <img src="{{ asset('images/MONITO.png') }}" alt="Imagen Descriptiva">
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const categoriaSelect = document.getElementById("categoria");
    const destinoSelect = document.getElementById("destino");

    // Función para mostrar/ocultar opciones
    function filtrarOpciones() {
        const categoria = categoriaSelect.value;

        // Limpiar el valor seleccionado en el select de destino
        destinoSelect.value = "";

        // Obtener todas las opciones del select de destino
        const opcionesDestino = Array.from(destinoSelect.options);

        // Ocultar todas las opciones inicialmente
        opcionesDestino.forEach((opcion) => {
            opcion.style.display = "none";
        });

        // Mostrar opciones según la categoría seleccionada
        switch (categoria) {
            case "EMS NAT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value === "local_1" ||
                        opcion.value === "local_2" ||
                        opcion.value === "local_3" ||
                        opcion.value === "local_4" ||
                        opcion.value.startsWith("nacional")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "EMS INT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "MI ENCOMIENDA":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("cui_cap") ||
                        opcion.value === "cui1" ||
                        opcion.value === "pro_dentro" ||
                        opcion.value === "pro_otro"
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "ENCOMIENDA":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "LC/AO NAT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value === "local_1" ||
                        opcion.value.startsWith("nacional") ||
                        opcion.value === "pro_dentro" ||
                        opcion.value === "pro_otro" ||
                        opcion.value === "cui1" ||
                        opcion.value === "cui2"
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "LC/AO INT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "ECA NAT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value === "local_1" ||
                        opcion.value.startsWith("nacional") ||
                        opcion.value === "pro_dentro" ||
                        opcion.value === "pro_otro" ||
                        opcion.value === "cui1" ||
                        opcion.value === "cui2"
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "ECA INT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "PLIEGOS NAT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value === "local_1" ||
                        opcion.value.startsWith("nacional") ||
                        opcion.value === "pro_dentro" ||
                        opcion.value === "pro_otro"
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "PLIEGOS INT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "SACAS M NAT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("nacional") ||
                        opcion.value === "pro_dentro"
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "SACAS M INT":
                opcionesDestino.forEach((opcion) => {
                    if (
                        opcion.value.startsWith("dest_a") ||
                        opcion.value.startsWith("dest_b") ||
                        opcion.value.startsWith("dest_c") ||
                        opcion.value.startsWith("dest_d") ||
                        opcion.value.startsWith("dest_e")
                    ) {
                        opcion.style.display = "block";
                    }
                });
                break;

            case "SUPER NAT":
                opcionesDestino.forEach((opcion) => {
                    if (opcion.value.startsWith("nacional")) {
                        opcion.style.display = "block";
                    }
                });
                break;

            default:
                // No mostrar opciones si no hay una categoría válida
                break;
        }
    }

    // Escuchar el evento de cambio en el select de categoría
    categoriaSelect.addEventListener("change", filtrarOpciones);

    // Ejecutar la función al cargar la página para asegurar el estado inicial
    filtrarOpciones();
});

</script>
