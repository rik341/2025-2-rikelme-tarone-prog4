document.addEventListener("DOMContentLoaded", async () => {

    const url = `temperature.php?formato=json&inicio=${dataInicial}&fim=${dataFinal}`;
    const resp = await fetch(url);
    const dados = await resp.json();

    const lista = dados.lista;
    const media = dados.media_periodo.temperatura_media;
    const maxmin = dados.max_min_med;

    // ------------------------------------------------------
    // 1) GRÁFICO DATA/HORA/TEMPERATURA - filtrando de 20 em 20
    // ------------------------------------------------------
    const filtroIntervalo = 20; // mostra 1 a cada 20 leituras
    const tempoLabels = lista
        .filter((_, index) => index % filtroIntervalo === 0)
        .map(l => `${l.data} ${l.hora}`);
    const tempValores = lista
        .filter((_, index) => index % filtroIntervalo === 0)
        .map(l => Number(l.temperatura));

    const ctx = document.getElementById("graficoTemperatura").getContext("2d");

    // Gradiente para a linha
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(255, 99, 132, 0.5)');
    gradient.addColorStop(1, 'rgba(255, 99, 132, 0)');

    new Chart(ctx, {
        type: "line",
        data: {
            labels: tempoLabels,
            datasets: [{
                label: "Temperatura (°C)",
                data: tempValores,
                borderColor: "rgba(255, 99, 132, 1)",
                backgroundColor: gradient,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: "red",
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0,0,0,0.8)'
                },
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: {
                x: {
                    display: true,
                    title: { display: true, text: 'Data / Hora' },
                    ticks: { maxRotation: 90, minRotation: 45, autoSkip: true }
                },
                y: { display: true, title: { display: true, text: 'Temperatura (°C)' } }
            }
        }
    });

    // ------------------------------------------------------
    // MOSTRAR TEMPERATURA MÉDIA COMO TEXTO
    // ------------------------------------------------------
    document.getElementById("valorMedia").textContent = `${Number(media).toFixed(2)} °C`;

    // ------------------------------------------------------
    // 2) GRÁFICO MAX / MIN / MED
    // ------------------------------------------------------
    new Chart(document.getElementById("graficoMaxMinMed"), {
        type: "bar",
        data: {
            labels: ["Máx", "Mín", "Média"],
            datasets: [{
                label: "°C",
                data: [
                    Number(maxmin.temp_maxima),
                    Number(maxmin.temp_minima),
                    Number(maxmin.temp_media)
                ],
                backgroundColor: ["red", "blue", "orange"]
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false } } }
    });

});
