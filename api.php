<?php

    header('Access-Control-Allow-Origin: *');

    require 'db.php';

    $data = filter_input(INPUT_POST, 'data', FILTER_DEFAULT);
    $result = [];

    if($data) {
        // Transforma o json em array
        $data = json_decode($data);

        try {
            switch ($data->action) {
                case 'getCursos':
                    $query = 'SELECT * FROM cursos';
                    $query = $conn->prepare($query);
                    if($query->execute()) {$result = $query->fetchAll(PDO::FETCH_OBJ);}
                    else {$result = array('error' => 'Erro ao buscar cursos');}
                    break;
    
                case 'getCursosFromIdAluno':
                    $query = 'SELECT c.* FROM cursos AS c JOIN alunos_cursos AS ac ON ac.id_curso = c.id WHERE ac.id_aluno = :id_aluno';
                    $query = $conn->prepare($query);
                    $query->bindValue(':id_aluno', $data->id_aluno);
                    if($query->execute()) {
                        $result = $query->fetchAll(PDO::FETCH_OBJ);
                    } else {$result = array('error' => 'Erro ao buscar cursos');}
                    break;
    
                // ALUNOS ----------------------------------------------------------
                
                case 'getAlunos':
                    $query = 'SELECT * FROM alunos';
                    $query = $conn->prepare($query);
                    if($query->execute()) {
                        $result = $query->fetchAll(PDO::FETCH_OBJ);
                    } else {$result = array('error' => 'Erro ao buscar alunos');}
                    break;
    
                case 'getAluno':
                    $query = 'SELECT * FROM alunos WHERE id = :id';
                    $query = $conn->prepare($query);
                    $query->bindValue(':id', $data->id);
                    if($query->execute()) {
                        $result = $query->fetch(PDO::FETCH_OBJ);
                    } else {$result = array('error' => 'Erro ao buscar aluno');}
                    break;

                case 'adicionarAluno':
                    // begin transaction para poder fazer o rollback caso algo dê errado em algum dos dois inserts
                    $conn->beginTransaction();
                    try {
                        $sql = "INSERT INTO alunos (nome, sexo, telefone, matricula, bilingue) VALUES (:nome, :sexo, :telefone, :matricula, :bilingue)";
                        $query = $conn->prepare($sql);
                        $query->bindValue(':nome', $data->nome);
                        $query->bindValue(':sexo', $data->sexo);
                        $query->bindValue(':telefone', $data->telefone);
                        $query->bindValue(':matricula', $data->matricula);
                        $query->bindValue(':bilingue', $data->bilingue);
                        if($query->execute()) {
                            $id_aluno = $conn->lastInsertId();
                            foreach ($data->cursos as $id_curso) {
                                $sql2 = "INSERT INTO alunos_cursos (id_aluno, id_curso) VALUES (:id_aluno, :id_curso)";
                                $query2 = $conn->prepare($sql2);
                                $query2->bindValue(':id_aluno', $id_aluno);
                                $query2->bindValue(':id_curso', $id_curso);
                                $query2->execute();
                            }
                            $conn->commit();
                            $result = array('ok' => true, 'status' => '200', 'message' => 'Aluno adicionado com sucesso');
                        } else {
                            $conn->rollBack();
                            $result = array('ok' => false, 'status' => '500', 'message' => 'Algo de errado no servidor!' );
                        }
                    } catch (Exception $ex) {
                        $conn->rollBack();
                        $result = array('ok' => false, 'status' => '500', 'message' => $ex );
                    }
                    break;
                
                case 'editarAluno':
                    $sql = "UPDATE alunos SET nome = :nome, sexo = :sexo, telefone = :telefone, matricula = :matricula, bilingue = :bilingue WHERE id = :id";
                    $query = $conn->prepare($sql);
                    $query->bindValue(':id', $data->id);
                    $query->bindValue(':nome', $data->nome);
                    $query->bindValue(':sexo', $data->sexo);
                    $query->bindValue(':telefone', $data->telefone);
                    $query->bindValue(':matricula', $data->matricula);
                        $data->bilingue = $data->bilingue == 'true' ? 1 : 0;
                    $query->bindValue(':bilingue', $data->bilingue);
    
                    if($query->execute()) {
    
                        // Aqui eu quero colocar o código para poder realizar a edição dos cursos do aluno na tabela intermediária alunos_cursos
                        $sql2 = "DELETE FROM alunos_cursos WHERE id_aluno = :id_aluno";
                        $query2 = $conn->prepare($sql2);
                        $query2->bindValue(':id_aluno', $data->id);
                        if($query2->execute()) {
                            foreach ($data->cursos as $id_curso) {
                                $sql3 = "INSERT INTO alunos_cursos (id_aluno, id_curso) VALUES (:id_aluno, :id_curso)";
                                $query3 = $conn->prepare($sql3);
                                $query3->bindValue(':id_aluno', $data->id);
                                $query3->bindValue(':id_curso', $id_curso);
                                $query3->execute();
                            }
                        } else {$result = array('status' => '500', 'message' => 'Algo de errado no servidor!' );}
    
                        $result = array('status' => '200', 'message' => 'Aluno editado com sucesso');
                    } else {$result = array('status' => '500', 'message' => 'Algo de errado no servidor!' );}
                    break;
                
                case 'excluirAluno':
                    $sql = "DELETE FROM alunos WHERE id = :idAluno";
                    $query = $conn->prepare($sql);
                    $query->bindValue(':idAluno', $data->idAluno);
                    if($query->execute()) {
                        $result = array('ok' => true, 'status' => '200', 'message' => 'Aluno excluído com sucesso');
                    } else {$result = array('ok' => false, 'status' => '500', 'message' => 'Algo de errado no servidor!' );}
                    break;
    
                default:
                    # code...
                    break;
            }

        } catch (Exception $ex) {
            $result = array('ok' => false, 'status' => '500', 'message' => $ex );
        }

        echo json_encode($result);

    }

?>