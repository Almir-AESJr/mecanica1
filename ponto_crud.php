<?php
require_once("valida_acesso.php");
?>
<?php
require_once("conexao.php");
require_once("ponto_filtro.php");




//operações via ajax
if (filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST") {
    if (!isset($_POST["acao"])) {
        return;
    }

    switch ($_POST["acao"]) {
        case "adicionar":
            try {
                $errosAux = "";

                $registro = new stdClass();
                $registro = json_decode($_POST['registro']);
                validaDadosPonto($registro);

                $sql = "insert into ponto(valor_corrente, valor_desgaste, valor_remocao, valor_rugosidade, polaridade, eletrodo_id, operacao_id, valor_relacao, duracao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->valor_corrente_ponto,
                    $registro->valor_desgaste_ponto,
                    $registro->valor_remocao_ponto,
                    $registro->valor_rugosidade_ponto,
                    $registro->polaridade_ponto,
                    $registro->eletrodo_id_ponto,
                    $registro->operacao_id_ponto,
                    $registro->valor_relacao_ponto,
                    $registro->duracao_ponto
                ));
                print json_encode($conexao->lastInsertId());
            } catch (Exception $e) {
                if (isset($_SESSION["erros"])) {
                    foreach ($_SESSION["erros"] as $chave => $valor) {
                        $errosAux .= $valor . "<br>";
                    }
                }
                $errosAux .= $e->getMessage();
                unset($_SESSION["erros"]);
                echo "Erro: " . $errosAux . "<br>";
            } finally {
                $conexao = null;
            }
            break;


        case "editar":
            try {
                $errosAux = "";

                $registro = new stdClass();
                $registro = json_decode($_POST['registro']);
                validaDadosPonto($registro);

                $sql = "update ponto set valor_corrente = ?, valor_desgaste = ?, valor_remocao = ?, valor_rugosidade = ?, polaridade = ?, eletrodo_id = ?, operacao_id = ?, valor_relacao = ?, duracao = ? where id = ? ";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->valor_corrente_ponto,
                    $registro->valor_desgaste_ponto,
                    $registro->valor_remocao_ponto,
                    $registro->valor_rugosidade_ponto,
                    $registro->polaridade_ponto,
                    $registro->eletrodo_id_ponto,
                    $registro->operacao_id_ponto,
                    $registro->valor_relacao_ponto, // Facrof
                    $registro->duracao_ponto,
                    $registro->id_ponto
                ));
                print json_encode(1);
            } catch (Exception $e) {
                foreach ($_SESSION["erros"] as $chave => $valor) {
                    $errosAux .= $valor . "<br>";
                }
                $errosAux .= $e->getMessage();
                unset($_SESSION["erros"]);
                echo "Erro: " . $errosAux . "<br>";
            } finally {
                $conexao = null;
            }
            break;



        case "excluir":
            try {
                $registro = new stdClass();
                $registro = json_decode($_POST["registro"]);

                $sql = "delete from ponto where id = ? ";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->id
                ));

                print json_encode(1);
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            } finally {
                $conexao = null;
            }
            break;


        case 'buscar':
            try {
                $registro = new stdClass();
                $registro = json_decode($_POST["registro"]);

                $sql = "select * from ponto where id = ?";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->id
                ));

                print json_encode($pre->fetchAll(PDO::FETCH_ASSOC));
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            } finally {
                $conexao = null;
            }
            break;


        case 'simulador':
            try {

                $eletrodo_id = $_POST["eletrodo_id"];
                $polaridade = $_POST["polaridade"];
                $valor_corrente = $_POST["valor_corrente"];
                $valor_relacao = $_POST["valor_relacao"];

                if ($valor_corrente == "" && $valor_relacao == "") {
                    echo " O campo corrente ou relacão deve ser informado!";
                    return false;
                }

                if ($valor_relacao != "" && $valor_corrente != "") {
                    $sql = "select p.id as id, m.descricao as material, e.descricao as eletrodo, o.descricao as operacao, p.polaridade, p.valor_corrente
                from ponto p, material m, eletrodo e, operacao o
                where p.eletrodo_id = e.id
                and e.material_id = m.id
                and p.operacao_id = o.id
                and p.eletrodo_id = ?
                and p.polaridade = ? 
                and p.valor_corrente = ?
                and p.valor_relacao = ?
                order by material, eletrodo, operacao, polaridade";
                } elseif ($valor_corrente != "" && $valor_relacao == "") {

                    $sql = "select p.id as id, m.descricao as material, e.descricao as eletrodo, o.descricao as operacao, p.polaridade, p.valor_corrente
                from ponto p, material m, eletrodo e, operacao o
                where p.eletrodo_id = e.id
                and e.material_id = m.id
                and p.operacao_id = o.id
                and p.eletrodo_id = ?
                and p.polaridade = ? 
                and p.valor_corrente = ?
                order by material, eletrodo, operacao, polaridade";
                } elseif ($valor_corrente == "" && $valor_relacao != "") {

                    $sql = "select p.id as id, m.descricao as material, e.descricao as eletrodo, o.descricao as operacao, p.polaridade, p.valor_corrente
                from ponto p, material m, eletrodo e, operacao o
                where p.eletrodo_id = e.id
                and e.material_id = m.id
                and p.operacao_id = o.id
                and p.eletrodo_id = ?
                and p.polaridade = ? 
                and p.valor_relacao = ?
                order by material, eletrodo, operacao, polaridade";
                }

                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);

                if ($valor_relacao != "" && $valor_corrente != "") {
                    $pre = $conexao->prepare($sql);
                    $pre->execute(array(
                        $eletrodo_id,
                        $polaridade,
                        $valor_corrente,
                        $valor_relacao,
                    ));
                } elseif ($valor_corrente != "" && $valor_relacao == "") {
                    $pre1 = $conexao->prepare($sql);
                    $pre1->execute(array(
                        $eletrodo_id,
                        $polaridade,
                        $valor_corrente,
                    ));
                } elseif ($valor_corrente == "" && $valor_relacao != "") {
                    $pre2 = $conexao->prepare($sql);
                    $pre2->execute(array(
                        $eletrodo_id,
                        $polaridade,
                        $valor_relacao,
                    ));
                }


                if ($valor_relacao != "" && $valor_corrente != "") {
                    print json_encode($pre->fetchAll(PDO::FETCH_ASSOC));
                } elseif ($valor_corrente != "" && $valor_relacao == "") {
                    print json_encode($pre1->fetchAll(PDO::FETCH_ASSOC));
                } elseif ($valor_corrente == "" && $valor_relacao != "") {
                    print json_encode($pre2->fetchAll(PDO::FETCH_ASSOC));
                }
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            } finally {
                $conexao = null;
            }
            break;
        case 'grafico':
            try {
                
                $valor_corrente = $_POST["valor_corrente"];
                $valor_relacao = $_POST["valor_relacao"];

                if ($valor_corrente == "" && $valor_relacao == "") {
                    echo " O campo corrente ou relacão deve ser informado!";
                    return false;
                }

                if ($valor_relacao != "" && $valor_corrente != "") {
                    $sql = "select p.duracao, p.valor_remocao 
                         from ponto as p
                         where valor_corrente = ? and valor_relacao = ?";
                } elseif ($valor_corrente != "" && $valor_relacao == "") {

                    $sql = "select p.duracao, p.valor_remocao 
                         from ponto as p
                         where valor_corrente = ?";
                } elseif ($valor_corrente == "" && $valor_relacao != "") {

                    $sql = "select p.duracao, p.valor_remocao 
                         from ponto as p
                        where valor_relacao = ?";
                }

                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);

                if ($valor_relacao != "" && $valor_corrente != "") {
                    $pre = $conexao->prepare($sql);
                    $pre->execute(array(
                        $valor_corrente,
                        $valor_relacao
                    ));
                } elseif ($valor_corrente != "" && $valor_relacao == "") {
                    $pre1 = $conexao->prepare($sql);
                    $pre1->execute(array(
                        $valor_corrente,
                    ));
                } elseif ($valor_corrente == "" && $valor_relacao != "") {
                    $pre2 = $conexao->prepare($sql);
                    $pre2->execute(array(
                        $valor_relacao,
                    ));
                }

                $results;

                if ($valor_relacao != "" && $valor_corrente != "") {
                    while ($results = $pre->fetch(PDO::FETCH_ASSOC)) {
                        $result[] = $results;
                    }
                } elseif ($valor_corrente != "" && $valor_relacao == "") {
                    while ($results = $pre1->fetch(PDO::FETCH_ASSOC)) {
                        $result[] = $results;
                    }
                } elseif ($valor_corrente == "" && $valor_relacao != "") {
                    while ($results = $pre2->fetch(PDO::FETCH_ASSOC)) {
                        $result[] = $results;
                    }
                }

                print json_encode($result);
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            } finally {
                $conexao = null;
            }
            break;
        default:
            print json_encode(0);
            return;
    }
}

//consulta sem ajax
function buscarPonto(int $id)
{
    try {
        $sql = "select * from ponto where id = ?";
        $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
        $pre = $conexao->prepare($sql);
        $pre->execute(array(
            $id
        ));

        return $pre->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "<br>";
    } finally {
        $conexao = null;
    }
}

//consulta sem ajax
// function listarPonto()
// {
//     try {
//         $sql = "select * from ponto order by descricao";
//         $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
//         $pre = $conexao->prepare($sql);
//         $pre->execute();

//         return $pre->fetchAll(PDO::FETCH_ASSOC);
//     } catch (Exception $e) {
//         echo "Erro: " . $e->getMessage() . "<br>";
//     } finally {
//         $conexao = null;
//     }
// }



// function buscarCorrente(int $valor_corrente)
// {
//     try {
//         $sql = "select * from ponto where valor_corrente = ?";
//         $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
//         $pre = $conexao->prepare($sql);
//         $pre->execute(array(
//             $valor_corrente
//         ));

//         return $pre->fetchAll(PDO::FETCH_ASSOC);
//     } catch (Exception $e) {
//         echo "Erro: " . $e->getMessage() . "<br>";
//     } finally {
//         $conexao = null;
//     }
// }


function listarCorrente()
{
    try {
        $sql = "select distinct(valor_corrente) from ponto;";
        $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
        $pre = $conexao->prepare($sql);
        $pre->execute();
        return $pre->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "<br>";
    } finally {
        $conexao = null;
    }
}



// function buscarRelacao(int $valor_relacao)
// {
//     try {
//         $sql = "select * from ponto where valor_relacao = ?";
//         $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
//         $pre = $conexao->prepare($sql);
//         $pre->execute(array(
//             $valor_relacao
//         ));

//         return $pre->fetchAll(PDO::FETCH_ASSOC);
//     } catch (Exception $e) {
//         echo "Erro: " . $e->getMessage() . "<br>";
//     } finally {
//         $conexao = null;
//     }
// }


function listarRelacao()
{
    try {
        $sql = "select distinct(valor_relacao) from ponto;";
        $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
        $pre = $conexao->prepare($sql);
        $pre->execute();
        return $pre->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "<br>";
    } finally {
        $conexao = null;
    }
}


function validarCorrenteRelacao($valor_corrente, $valor_relacao)
{
    $corrente = $valor_corrente;
    $relaco = $valor_relacao;

    if ($corrente == 18 || $relaco == "") {
        echo ("O campo corrente (" . $corrente . ") e relacao (" . $relaco . ") estão corretos!");
        return true;
    }
}
