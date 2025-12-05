window.addEventListener("load", () => {
    const loading = document.getElementById("loading");
    const canvas = document.getElementById("graficoAqi");
    const intervaloInput = document.getElementById("intervalo");
    let chartAqi = null;

    //converte as datas no formato brasileiro

    function converterParaDateBrasil(dataStr) {
        const [data, hora] = dataStr.split(" ");
        const [dia, mes, ano] = data.split("/");
        const [h, m, s] = hora.split(":");
        return new Date(`${ano}-${mes}-${dia}T${h}:${m}:${s}`);
    }

    function formatarLabelBR(dataStr) {
        const d = converterParaDateBrasil(dataStr);
        return d.toLocaleString("pt-BR", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    // AGORA FUNCIONA — intervalo lido corretamente
    const intervaloUsado = parseInt(intervaloInput.value) || 20;

//pega dados php
    fetch(`ptqa_aqi.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
        .then(res => res.json())
        .then(dados => {

            loading.style.display = "none";

            if (!dados.ruim || dados.ruim.length === 0) {
                loading.style.display = "block";
                loading.textContent = "Nenhum registro AQI ≥ 4 encontrado no período.";
                return;
            }

            const labelsBruto = dados.ruim.map(r => r.datahora_completa);
            const valoresBruto = dados.ruim.map(r => parseFloat(r.aqi));

            const labelsFiltrados = labelsBruto
                .filter((_, i) => i % intervaloUsado === 0)
                .map(s => formatarLabelBR(s));

            const valoresFiltrados = valoresBruto
                .filter((_, i) => i % intervaloUsado === 0);

            if (chartAqi) chartAqi.destroy();

            const ctx = canvas.getContext("2d");
            chartAqi = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labelsFiltrados,
                    datasets: [{
                        label: "Baixa Qualidade do Ar (AQI ≥ 4)",
                        data: valoresFiltrados,
                        borderColor: "rgba(200,50,50,1)",
                        backgroundColor: "rgba(200,50,50,0.2)",
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: "Data e Hora (BR)" } },
                        y: { title: { display: true, text: "AQI" }, beginAtZero: true }
                    }
                }
            });
        })
        .catch(() => {
            loading.textContent = "Erro ao carregar dados.";
        });
});
