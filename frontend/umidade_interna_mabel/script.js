document.addEventListener("DOMContentLoaded", async () => {

    const resposta = await fetch("umi_interna.php?formato=json");
    const json = await resposta.json();

    // Média
    const media = parseFloat(json.media);

    document.getElementById("MediaUmidade").textContent =
        isNaN(media) ? "--" : media.toFixed(2);

    // Gráfico
    const dados = json.dados;

    const labels = dados.map(item => item.datahora_completa);
    const valores = dados.map(item => parseFloat(item.hi));

    const ctx = document.getElementById("graficoUmidadeInterna").getContext("2d");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: "Umidade Interna (%)",
                data: valores,
                borderColor: "blue",
                borderWidth: 2.5,
                pointRadius: 2,
                tension: 0.3
            }]
        },
        options: {
            responsive: true
        }
    });

});
