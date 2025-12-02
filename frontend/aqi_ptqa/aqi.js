window.addEventListener("DOMContentLoaded", () => {
  const loading = document.getElementById("loading");
  const canvas = document.getElementById("graficoAqi");

  // 🔥 SUA FUNÇÃO (agora aplicada no gráfico)
  function converterParaDateBrasil(dataStr) {
      // dataStr vem assim: "07/06/2025 14:32:10"

      const [data, hora] = dataStr.split(" ");
      const [dia, mes, ano] = data.split("/");
      const [h, m, s] = hora.split(":");

      // Converte para formato americano válido para o Date()
      return new Date(`${ano}-${mes}-${dia}T${h}:${m}:${s}`);
  }

  // Formata para exibição no gráfico
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

  fetch(`ptqa_aqi.php?inicio=${dataInicial}&fim=${dataFinal}&formato=json`)
      .then(res => res.json())
      .then(dados => {
          loading.style.display = "none";

          if (dados.ruim.length === 0) {
              loading.style.display = "block";
              loading.textContent = "Nenhum registro AQI ≥ 4 encontrado no período.";
              return;
          }

          // labels convertidos pela função que você pediu
          const labels = dados.ruim.map(d => formatarLabelBR(d.datahora_completa));
          const valores = dados.ruim.map(d => parseFloat(d.aqi));

          const ctx = canvas.getContext("2d");

          new Chart(ctx, {
              type: "line",
              data: {
                  labels: labels,
                  datasets: [{
                      label: "Baixa Qualidade do Ar (AQI ≥ 4)",
                      data: valores,
                      borderColor: "rgba(200, 50, 50, 1)",
                      backgroundColor: "rgba(200, 50, 50, 0.2)",
                      fill: true,
                      tension: 0.3,
                      pointRadius: 3
                  }]
              },
              options: {
                  responsive: true,
                  scales: {
                      x: {
                          title: {
                              display: true,
                              text: "Data e Hora (BR)"
                          }
                      },
                      y: {
                          title: {
                              display: true,
                              text: "AQI"
                          },
                          beginAtZero: true
                      }
                  }
              }
          });
      })
      .catch(() => {
          loading.textContent = "Erro ao carregar dados.";
      });
});
