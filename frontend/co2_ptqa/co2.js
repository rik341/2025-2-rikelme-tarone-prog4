document.addEventListener("DOMContentLoaded", async () => {

    const dataInicial = document.getElementById("inicio").value || "2025-06-01";
    const dataFinal = document.getElementById("fim").value || "2025-06-30";
    const intervalo = parseInt(document.getElementById("intervalo").value) || 20;

    const url = `co2.php?formato=json&inicio=${dataInicial}&fim=${dataFinal}`;
    const resp = await fetch(url);
    const dados = await resp.json();

    document.getElementById("loading").style.display = "none";

    /* -----------------------------------------
       1) GRÁFICO CO₂ ACIMA DE 1000 ppm COM INTERVALO
    ----------------------------------------- */
    let labelsAcima = dados.co2_acima_1000.map(l => `${l.data} ${l.hora}`);
    let valoresAcima = dados.co2_acima_1000.map(l => Number(l.eco2));

    // Filtro de intervalo apenas neste gráfico
    labelsAcima = labelsAcima.filter((_, i) => i % intervalo === 0);
    valoresAcima = valoresAcima.filter((_, i) => i % intervalo === 0);

    new Chart(document.getElementById("graficoAcima"), {
        type: "line",
        data: {
            labels: labelsAcima,
            datasets: [{
                label: "CO₂ acima de 1000 ppm",
                data: valoresAcima,
                borderColor: "red",
                borderWidth: 2,
                fill: false,
                pointRadius: 3,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: "Data e Hora" } },
                y: { title: { display: true, text: "ppm" }, beginAtZero: false }
            }
        }
    });

    /* -----------------------------------------
       2) CO₂ MÁXIMO NO PERÍODO
    ----------------------------------------- */
    const maximo = dados.co2_maximo_periodo.co2_maximo ?? 0;
    document.getElementById("co2Max").textContent =
        `Maior CO₂ registrado: ${maximo} ppm`;

    /* -----------------------------------------
       3) TOP 5 MÉDIAS DE CO₂ DO MÊS
    ----------------------------------------- */
    const labelsTop = dados.top5_medias_co2.map(l => l.dia);
    const valoresTop = dados.top5_medias_co2.map(l => Number(l.media_co2));

    new Chart(document.getElementById("graficoTop5"), {
        type: "bar",
        data: {
            labels: labelsTop,
            datasets: [{
                label: "Média de CO₂ (ppm)",
                data: valoresTop,
                backgroundColor: "green"
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: "Dia" } },
                y: { title: { display: true, text: "ppm" }, beginAtZero: false }
            }
        }
    });
});
