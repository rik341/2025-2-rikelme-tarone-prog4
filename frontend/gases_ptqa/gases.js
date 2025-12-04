document.addEventListener("DOMContentLoaded", () => {

    const inputIntervalo = document.getElementById("intervalo");
    inputIntervalo.value = intervaloPadrao;

    fetch(`${urlBase}?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
        .then(r => r.json())
        .then(dados => {

            const intervalo = parseInt(inputIntervalo.value) || 20;

            /* -------------------------------
               GRÁFICO 1 — TVOC > 200 ppb
               ------------------------------- */

            const labelsBruto  = dados.gases_acima_200.map(d => d.datahora);
            const valoresBruto = dados.gases_acima_200.map(d => Number(d.tvoc));

            // Filtro de X em X leituras
            const labels1  = labelsBruto.filter((_, i) => i % intervalo === 0);
            const valores1 = valoresBruto.filter((_, i) => i % intervalo === 0);

            new Chart(document.getElementById("graficoGasesAcima"), {
                type: "line",
                data: {
                    labels: labels1,
                    datasets: [{
                        label: `TVOC > 200 ppb (intervalo ${intervalo})`,
                        data: valores1,
                        borderColor: "red",
                        backgroundColor: "rgba(255, 0, 0, 0.2)",
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    }]
                }
            });

            /* -------------------------------
               GRÁFICO 2 — MÉDIA POR AQI
               ------------------------------- */
            const labels2  = dados.media_aqi.map(d => "AQI " + d.aqi);
            const valores2 = dados.media_aqi.map(d => Number(d.media_tvoc));

            new Chart(document.getElementById("graficoMediaAQI"), {
                type: "bar",
                data: {
                    labels: labels2,
                    datasets: [{
                        label: "Média TVOC (ppb)",
                        data: valores2,
                        backgroundColor: "blue"
                    }]
                }
            });
        })
        .catch(() => alert("Erro ao carregar os dados."));
});


/* -------------------------------------
   SUBMISSÃO DO FORMULÁRIO COM INTERVALO
---------------------------------------- */

document.getElementById("formPeriodo").addEventListener("submit", e => {
    const intervalo = document.getElementById("intervalo").value;
    const inicio = e.target.inicio.value;
    const fim = e.target.fim.value;

    // mantém o valor do intervalo no GET
    e.target.action = `${urlBase}?inicio=${inicio}&fim=${fim}&intervalo=${intervalo}`;
});
