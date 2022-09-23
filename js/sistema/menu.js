//para funcionar navegação via ajax os ids devem ser únicos em cada tela
$(document).ready(function () {
    //clicar no botão da div de erros e escondendo as mensagens de erros
    $("#div_mensagem_botao_menu").click(function () {
        $("#div_mensagem_menu").hide();
    });

    $("#simulador_link").click(function () {
        $(location).prop("href", "menu.php");
    });

    $("#material_link").click(function (e) {
        $("#conteudo").load("material_index.php");
    });

    $("#eletrodo_link").click(function (e) {
        $("#conteudo").load("eletrodo_index.php");
    });

    $("#operacao_link").click(function (e) {
        $("#conteudo").load("operacao_index.php");
    });

    $("#ponto_link").click(function (e) {
        $("#conteudo").load("ponto_index.php");
    });

    $("#usuario_link").click(function (e) {
        $("#conteudo").load("usuario_index.php");
    });

    $("#logout_modal_sim").click(function (e) {
        $(location).attr("href", "logout.php");
    });

    $("#sobre_link").click(function () {
        $("#sobre_modal").modal("show");
    });

    $("#logout_link").click(function () {
        $("#logout_modal").modal("show");
    });

    $('#botao_pesquisar_simulador').click(function (e) {
        var eletrodo = $("#eletrodo_simulador option:selected").val();
        var polaridade = $("#polaridade_simulador option:selected").val();
        var corrente = $("#corrente option:selected").val();
        var relacao = $("#relacao option:selected").val();
        var html = "";

        //regerando o canvas para não ter erro no gráfico
        $("#div_ponto").html("");
        $("#div_mensagem_menu").hide();

        $.ajax({
            type: "POST",
            cache: false,
            url: "ponto_crud.php",
            data: {
                acao: "simulador",
                eletrodo_id: eletrodo,
                polaridade: polaridade,
                valor_corrente: corrente,
                valor_relacao: relacao
            },
            dataType: "json",
            success: function (data) {
                if (data.length > 0) {
                    html = "<table class='table table-striped table-hover'>";
                    html += "<thead class='table-info'>";
                    html += "<tr>";
                    html += "<th scope='col'>ID</th>";
                    html += "<th scope='col'>Material</th>";
                    html += "<th scope='col'>Eletrodo</th>";
                    html += "<th scope='col'>Polaridade</th>";
                    html += "<th scope='col'>Operação</th>";
                    html += "<th scope='col'>Ação</th>";
                    html += "</tr></thead><tbody>";
                    $.each(data, function (i, item) {
                        html += "<tr>";
                        html += "<td>" + item.id + "</td>";
                        html += "<td>" + item.material + "</td>";
                        html += "<td>" + item.eletrodo + "</td>";
                        html += "<td>" + item.polaridade + "</td>";
                        html += "<td>" + item.operacao + "</td>";
                        html += "<td>" + "<a id='botao_grafico_ponto' chave='" + item.id + "' class='btn btn-info btn-sm' title='Gráfico'><i class='fas fa-chart-bar'></i></a>" + "</td>";
                        html += "</tr>";
                    });
                    html += "</tbody></table>";

                    $("#div_ponto").append(html);
                } else {
                    $("#div_mensagem_texto_menu").empty().append("Nenhum ponto encontrado!");
                    $("#div_mensagem_menu").show();
                }
            },
            error: function (e) {
                $("#div_mensagem_texto_menu").empty().append(e.responseText);
                $("#div_mensagem_menu").show();
            },
            beforeSend: function () {
                $("#carregando_menu").removeClass("d-none");
            },
            complete: function () {
                $("#carregando_menu").addClass("d-none");
            }
        });
    });

    //botão visualizar da tela de listagem de registros
    $(document).on("click", "#botao_grafico_ponto", function (e) {
        e.stopImmediatePropagation();
        //levando os elementos para tela de consulta para depois realizar as buscas/pesquisas
        var id = $(this).attr("chave");
        //var eletrodo = $("#eletrodo_simulador option:selected").val();
        var corrente = $("#corrente").val();
        var relacao = $("#relacao").val();

        $("#div_ponto").html("");
        $("#div_ponto").append("<canvas id='grafico'></canvas>");

        $.ajax({
            type: "POST",
            cache: false,
            url: "ponto_crud.php",
            data: {
                acao: "grafico",
                valor_corrente: corrente,
                valor_relacao: relacao,
            },
            dataType: "json",
            success: function (data) {
                var duracaoArray = [];
                var correnteArray = [];
                var remocaoArray = [];
                
                for (var i = 0; i < data.length; i++) {
                    duracaoArray.push(data[i].duracao);
                    correnteArray.push(data[i].valor_corrente);
                    remocaoArray.push(data[i].valor_remocao);
                  }

                  grafico(duracaoArray, correnteArray, remocaoArray);

                
            }
        });


        function grafico(duracao, corrente, remocao) {


            var ctx = document.getElementById("grafico").getContext('2d')

            var chart = new Chart(ctx, {

              type: 'line',
              data: {
                labels: duracao,
                datasets: [
                  {
                    label: 'Corrente',
                    backgroundColor: 'rgb(20, 199, 132)',
                    //borderColor: ['red', 'blue', 'green', 'yellow'],
                    borderColor: 'rgb(120, 99, 132)',
                    data: corrente
                  },
                  {
                    label: 'Remocao',
                    backgroundColor: 'rgb(100, 170, 160)',
                    //borderColor: ['red', 'blue', 'green', 'yellow'],
                    borderColor: 'rgb(50, 130, 130)',
                    data: remocao
                  }]
            },

              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Corrente "
                    }
                },
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: "Taxa de Remoção",
                            font: {
                                weight: "bold",
                            }
                        }
                    },
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: "Duração",
                            font: {
                                weight: "bold",
                            }
                        }
                    }
                }
              }          
            });


        }





        

       // alert("Implementar a parte do gráfico! ID: " + id);
    });











    // populando os eletrodos conforme altera o material
    $("#material_simulador").change(function (e) {
        e.stopImmediatePropagation();

        $("#carregando_menu").removeClass("d-none");

        $.ajax({
            type: "POST",
            cache: false,
            url: "eletrodo_crud.php",
            data: {
                acao: "simulador",
                id: $("#material_simulador option:selected").val()
            },
            dataType: "json",
            success: function (e) {
                var html = "";
                $.each(e, function (i, item) {
                    html += "<option value='" + item.id + "'>" + item.descricao + "</option>";
                });
                $("#eletrodo_simulador").html(html);
            },
            error: function (e) {
                $("#div_mensagem_texto_menu").empty().append(e.responseText);
                $("#div_mensagem_menu").show();
            },
            complete: function () {
                $('#carregando_menu').css({
                    display: "none"
                });
            }
        });
    });

    // chamando o evento para carregar os eletrodos ao iniciar a página
    $("#material_simulador").change();

    const showNavbar = (toggleId, navId, bodyId, headerId) => {
        const toggle = document.getElementById(toggleId),
            nav = document.getElementById(navId),
            bodypd = document.getElementById(bodyId),
            headerpd = document.getElementById(headerId)

        // Validate that all variables exist
        if (toggle && nav && bodypd && headerpd) {
            toggle.addEventListener('click', () => {
                // show navbar
                nav.classList.toggle('showtab')
                // change icon
                toggle.classList.toggle('fa-times')
                // add padding to body
                bodypd.classList.toggle('body')
                // add padding to header
                headerpd.classList.toggle('body')
            })
        }
    }

    showNavbar('header-toggle', 'nav-bar', 'body', 'header');

    /*===== LINK ACTIVE =====*/
    const linkColor = document.querySelectorAll('.nav_link');

    function colorLink() {
        if (linkColor) {
            linkColor.forEach(l => l.classList.remove('activemenu'));
            this.classList.add('activemenu');
        }
    }
    linkColor.forEach(l => l.addEventListener('click', colorLink));


});


