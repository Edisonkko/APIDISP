<?php

class citas_Ctrl {
    
    public function __construct() {
        $this->M_citas = new M_Citas();
        $this->M_medicos = new M_Medicos();
        $this->M_paciente = new M_Paciente();
        $this->M_disponibilidad=new M_Disponibilidad();
        $this->M_especialidades=new M_Especialidades();
    }
    
    public function crearCita($f3) {
        $id = 0; // Variable para almacenar el ID de la cita
        $msg = ""; // Variable para almacenar el mensaje de respuesta
    
        // Verificar si el paciente existe por su ID
        $paciente = $this->M_paciente->load(['PAC_ID = ?', $f3->get('POST.pac_id')]);
        if (!$paciente) {
            $msg = "El paciente no existe.";
        } else {
            // Verificar si el médico existe por su ID
            $medico = $this->M_medicos->load(['MED_ID = ?', $f3->get('POST.med_id')]);
            if (!$medico) {
                $msg = "El médico no existe.";
            } else {
                // Verificar si la disponibilidad existe por su ID
                $disponibilidad = new M_disponibilidad();
                $disponibilidad->load(['DIP_ID = ?', $f3->get('POST.disp_id')]);
                if (!$disponibilidad) {
                    $msg = "La disponibilidad no existe.";
                } else {
                    // Verificar si la cita ya está ocupada o tiene estado "Anulada"
                    $citaExistente = new M_citas();
                    $citaExistente->load(['FECHA = ? AND HORA_ID = ? AND MED_ID = ?', $f3->get('POST.fecha'), $f3->get('POST.hora_id'), $f3->get('POST.med_id')]);
                    if ($citaExistente->loaded() && $citaExistente->get('CITAS_ESTADO') !== 'Anulada') {
                        $msg = "La cita ya está ocupada.";
                    } else {
                        // Crear una nueva cita médica
                        $citaMedica = new M_citas();
                        $citaMedica->set('PAC_ID', $f3->get('POST.pac_id'));
                        $citaMedica->set('MED_ID', $f3->get('POST.med_id'));
                        $citaMedica->set('FECHA', $f3->get('POST.fecha'));
                        $citaMedica->set('HORA_ID', $f3->get('POST.hora_id'));
                        $citaMedica->set('CITAS_ESTADO', 'Confirmada'); // Estado predeterminado (puedes ajustarlo según tus necesidades)
    
                        // Guardar la cita médica en la base de datos
                        if ($citaMedica->save()) {
                            $id = $citaMedica->get('CITAS_ID');
                            $msg = "Cita médica agendada con éxito.";
                        } else {
                            $msg = "Error al agendar la cita médica.";
                        }
                    }
                }
            }
        }
    
        // Devolver la respuesta en formato JSON
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }
    
    
    public function getCitasAgendadas($f3) {
        $cadenaSQL = "SELECT cm.*, p.PER_NOM AS nombre_paciente, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID;        
    ";
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }

    public function listarCitasxID($f3) {
        $parametroID = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT cm.*, p.PER_NOM AS nombre_paciente, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE cm.PAC_ID = ?
        AND (cm.CITAS_ESTADO = "Confirmada" OR cm.CITAS_ESTADO = "Pendiente");';
        $items = $f3->DB->exec($cadenaSQL, $parametroID);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }
    public function listarCitasMedicasxID($f3) {
        $parametroID = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT cm.*, p.PER_NOM AS nombre_paciente, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE cm.CITAS_ID = ?
        AND (cm.CITAS_ESTADO = "Confirmada" OR cm.CITAS_ESTADO = "Pendiente");';
        $items = $f3->DB->exec($cadenaSQL, $parametroID);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }

    public function listarCitasMxID($f3) {
        $parametroID = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT cm.*, p.PER_NOM AS nombre_paciente, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE m.PER_ID = ?';
        $items = $f3->DB->exec($cadenaSQL, $parametroID);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }

    public function listarHorarios($f3){
        $cadenaSQL = 'SELECT * FROM horario';
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }

    public function listarEspecialidades($f3){
        $cadenaSQL = 'SELECT * FROM especialidades';
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }
    public function listarDoctores($f3){
        $cadenaSQL = 'SELECT  DISTINCT persona.*, usuarios.USR_CORREO,usuarios.USR_PASS, roles.ROL_DESC,medicos.MED_ID, especialidades.ESP_NOM, especialidades.ESP_ID
        FROM persona
        INNER JOIN usuarios ON persona.USR_ID = usuarios.USR_ID
        INNER JOIN roles ON usuarios.ROL_ID = roles.ROL_ID
        INNER JOIN medicos ON persona.PER_ID = medicos.PER_ID
        INNER JOIN especialidades ON medicos.ESP_ID = especialidades.ESP_ID;
        ';
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }

    public function getListarAdmin($f3){
        $cadenaSQL='SELECT persona.*, usuarios.USR_CORREO,usuarios.USR_PASS, roles.ROL_DESC
        FROM persona
        INNER JOIN usuarios ON persona.USR_ID = usuarios.USR_ID
        INNER JOIN roles ON usuarios.ROL_ID = roles.ROL_ID
        WHERE roles.ROL_ID=1';
        $items=$f3->DB->exec($cadenaSQL);
        echo json_encode([
        'mensaje'=>count($items)>0? 'Hay registro':'No existe informacion',
        'cantidad'=>count($items),
        'info'=>[
            'items'=>$items
           
        ]
        ]);
    }

    public function listarDiaDisponiblesDocxID($f3){
        $parametroID = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT m.PER_ID, m.MED_ID, d.DIP_ID, ds.DIA, d.HORA_INICIO, d.HORA_FIN
        FROM medicos m
        INNER JOIN disponibilidad d ON m.MED_ID = d.MED_ID
        INNER JOIN dia_semana ds ON d.DIA_ID = ds.DIA_ID
        WHERE m.PER_ID = ?;        
        ';
        $items = $f3->DB->exec($cadenaSQL,$parametroID);
        
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }
    public function listarDia($f3){
        $cadenaSQL = 'SELECT * FROM dia_semana';
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }


    /*  SELECT m.*, e.ESP_NOM AS especialidad, p.PER_NOM AS medico_nombre
FROM medicos AS m
INNER JOIN especialidades AS e ON m.ESP_ID = e.ESP_ID
INNER JOIN persona AS p ON m.PER_ID = p.PER_ID
INNER JOIN disponibilidad d ON m.DIP_ID = d.DIP_ID
LEFT JOIN citas_medicas cm ON m.MED_ID = cm.MED_ID
WHERE cm.MED_ID IS NULL
  AND d.HORA_FIN > CURRENT_TIME  -- Filtrar disponibilidad futura
  AND NOT EXISTS (
    SELECT 1
    FROM citas_medicas cm2
    WHERE cm2.MED_ID = m.MED_ID
      AND cm2.FECHA = CURRENT_DATE  -- Filtrar citas para el día actual
  );  */
    public function listarDoctoresEspecialidad($f3) {
        $parametrofecha = $f3->get('PARAMS.fecha');
        $cadenaSQL = 'SELECT DISTINCT  e.ESP_NOM AS especialidad
        FROM especialidades e 
        INNER JOIN medicos m ON m.ESP_ID = e.ESP_ID
        INNER JOIN disponibilidad d ON m.MED_ID = d.MED_ID
        INNER JOIN dia_semana ds ON d.DIA_ID = ds.DIA_ID
        WHERE ds.DIA = 
              CASE DAYOFWEEK(?)
                WHEN 2 THEN "Lunes"
                WHEN 3 THEN "Martes"
                WHEN 4 THEN "Miercoles"
                WHEN 5 THEN "Jueves"
                WHEN 6 THEN "Viernes"
              END
        AND ? >= CURRENT_DATE;
        
        ';
        $items = $f3->DB->exec($cadenaSQL,[$parametrofecha,$parametrofecha]);
    
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registros',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existen medicos disponibles en la fecha elegida',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }

    public function listarDoctoresdisponibilidad($f3) {
        $parametroID  = $f3->get('PARAMS.id');
        $parametrofecha = $f3->get('PARAMS.fecha');
        $parametroEsp = $f3->get('PARAMS.esp');
        $cadenaSQL = 'SELECT DISTINCT m.MED_ID, m.ESP_ID, d.DIP_ID,p.PER_NOM AS medico_nombre
        FROM medicos m
        INNER JOIN especialidades e ON m.ESP_ID = e.ESP_ID
        INNER JOIN disponibilidad d ON m.MED_ID = d.MED_ID
        INNER JOIN dia_semana ds ON d.DIA_ID = ds.DIA_ID
        LEFT JOIN citas_medicas cm ON m.MED_ID = cm.MED_ID
        LEFT JOIN persona p ON m.PER_ID = p.PER_ID
        WHERE ds.DIA = 
          CASE DAYOFWEEK(?)
            WHEN 2 THEN "Lunes"
            WHEN 3 THEN "Martes"
            WHEN 4 THEN "Miercoles"
            WHEN 5 THEN "Jueves"
            WHEN 6 THEN "Viernes"
          END
          AND e.ESP_NOM=?;        
        ';
        $items = $f3->DB->exec($cadenaSQL,[$parametrofecha,$parametroEsp]);
    
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registros',
                'cantidad' => count($items),
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existen medicos disponibles',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
    }
    
    public function creaDisponibilidadDoc($f3){
        $id = 0; // Variable para almacenar el ID del medico
        $msg = ""; // Variable para almacenar el mensaje de respuesta
       
        $disponibilidad = new M_disponibilidad();
        $disponibilidad->load(['DIP_ID = ?', $f3->get('POST.id')]);
        if ($disponibilidad->loaded() > 0) {
            $msg = "disponibilidad registrado.";
        } else {
            // Crear una nueva cita médica
            $newDisp = new M_disponibilidad();
            $newDisp->set('DIP_ID', $f3->get('POST.dip_id'));
            $newDisp->set('MED_ID', $f3->get('POST.med_id'));
            $newDisp->set('DIA_ID', $f3->get('POST.dia_id'));
            $newDisp->set('HORA_INICIO', $f3->get('POST.hora_inicio'));
            $newDisp->set('HORA_FIN', $f3->get('POST.hora_fin'));

            // Guardar la cita médica en la base de datos
            if ($newDisp->save()) {
                $id = $newDisp->get('DIP_ID');
                $msg = "Disponibilidad registrado.";
            } else {
                $msg = "Error al registrar la disponibilidad.";
            }
        }
    
        // Devolver la respuesta en formato JSON
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }

    public function eliminarDisponibilidadDoc($f3) {
        $mensaje = "";
        $id = 0;
        $parametroIdPer = $f3->get('PARAMS.med_id');
        $parametroIdDisp = $f3->get('PARAMS.dia_id'); // se envía por la URL
        

        $this->M_disponibilidad->load(['MED_ID=? AND DIA_ID=?', $parametroIdPer,$parametroIdDisp]);
    
        if ($this->M_disponibilidad->loaded() > 0) {
            // Eliminar persona
            $this->M_disponibilidad->erase();
    
            $mensaje = "Cita eliminada con éxito";
            $id = 1;
        } else {
            $mensaje = "No existe la persona para eliminar";
        }
    
        echo json_encode([
            'mensaje' => $mensaje,
            'id' => $id
        ]);
    }
    public function actualizarDisponibilidad($f3){
        $dispID = $f3->get('PARAMS.id'); 
        $this->M_disponibilidad->load(['DIP_ID=?', $dispID]);
        $msg = "";
        $info = array();
        if ($this->M_disponibilidad->loaded() > 0) {
            $disp = new M_disponibilidad();
            $disp->load(['DIP_ID = ?', $dispID,]);
            if ($disp->loaded() > 0) {
                $this->M_disponibilidad->set('HORA_INICIO', $f3->get('POST.horaInicio'));
                $this->M_disponibilidad->set('HORA_FIN', $f3->get('POST.horaFin'));
                $this->M_disponibilidad->save();
    
                $info['id'] = $this->M_disponibilidad->get('DIP_ID');
                $msg = "La Disponibilidad se actualizó con éxito";
            }else {
                $msg = "No se pudo modificar la Disponibilidad";
                $info['id'] = 0;
            }
        }else {
            $msg = "La Disponibilidad no existe";
            $info['id'] = 0;
        }
        
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }
    
    public function actualizarCitaP($f3) {
        $citaID = $f3->get('PARAMS.id'); // Recuperando el ID de la cita que se quiere actualizar
        $this->M_citas->load(['CITAS_ID=?', $citaID]);
        $msg = "";
        $info = array();
    
        if ($this->M_citas->loaded() > 0) {
            // Proceso para actualizar la cita
            $cita = new M_citas();
            $cita->load(['CITAS_ID = ?', $citaID,]);
    
            if ($cita->loaded() > 0) {
                $this->M_citas->set('MED_ID', $f3->get('POST.med_id'));
                $this->M_citas->set('FECHA', $f3->get('POST.fecha'));
                $this->M_citas->set('HORA_ID', $f3->get('POST.hora_id'));
                $this->M_citas->set('CITAS_ESTADO', $f3->get('POST.estado'));
                $this->M_citas->save();
    
                $info['id'] = $this->M_citas->get('CITAS_ID');
                $msg = "La cita se modificó con éxito";
            } else {
                $msg = "No se pudo modificar la cita";
                $info['id'] = 0;
            }
        } else {
            $msg = "La cita no existe";
            $info['id'] = 0;
        }
        
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }

    public function actualizarMed($f3){
        $medID = $f3->get('PARAMS.id'); // Recuperando el ID de la cita que se quiere actualizar
        $this->M_medicos->load(['MED_ID=?', $medID]);
        $msg = "";
        $info = array();
    
        if ($this->M_medicos->loaded() > 0) {
            // Proceso para actualizar la cita
            $cita = new M_medicos();
            $cita->load(['MED_ID = ?', $medID,]);
    
            if ($cita->loaded() > 0) {
                $this->M_medicos->set('PER_ID', $f3->get('POST.per_id'));
                $this->M_medicos->set('ESP_ID', $f3->get('POST.esp_id'));
                $this->M_medicos->save();
    
                $info['id'] = $this->M_medicos->get('MED_ID');
                $msg = "El médico se modificó con éxito";
            } else {
                $msg = "No se pudo modificar el médico";
                $info['id'] = 0;
            }
        } else {
            $msg = "El medico no existe";
            $info['id'] = 0;
        }
        
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }
    

    public function eliminarCita($f3) {
        $mensaje = "";
        $id = 0;
        $parametroId = $f3->get('PARAMS.id'); // se envía por la URL
    
        $this->M_citas->load(['CITAS_ID=?', $parametroId]);
    
        if ($this->M_citas->loaded() > 0) {
            // Eliminar persona
            $this->M_citas->erase();
    
            $mensaje = "Cita eliminada con éxito";
            $id = 1;
        } else {
            $mensaje = "No existe la persona para eliminar";
        }
    
        echo json_encode([
            'mensaje' => $mensaje,
            'id' => $id
        ]);
    }

    #CHATBOT

    public function listarHoraxFecha($f3) {
        $parametrofecha = $f3->get('PARAMS.fecha');
        $parametroid = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT H.HORA_ID, H.NOM_HORA
                    FROM horario H
                    LEFT JOIN (
                        SELECT HORA_ID, FECHA, MED_ID, MAX(CITAS_ESTADO) AS CITAS_ESTADO
                        FROM citas_medicas
                        WHERE FECHA = ? AND MED_ID = ?
                        GROUP BY HORA_ID, FECHA, MED_ID
                    ) C ON H.HORA_ID = C.HORA_ID
                    WHERE (C.CITAS_ESTADO IS NULL OR C.CITAS_ESTADO = "Anulada")
                        AND CONCAT(?, " ", H.NOM_HORA) >= NOW();
                    ';
        $items = $f3->DB->exec($cadenaSQL, [$parametrofecha,$parametroid,$parametrofecha]);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }

    public function listarcitaxFecha($f3) {
        $parametroID = $f3->get('PARAMS.id');
        $parametroFecha = $f3->get('PARAMS.fecha');
        $cadenaSQL = 'SELECT cm.*, p.PER_NOM AS nombre_paciente, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE cm.PAC_ID = ? AND cm.FECHA = ?';
        $items = $f3->DB->exec($cadenaSQL,[$parametroID, $parametroFecha]);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }

    public function ModificarEstado($f3){
        $cadenaSQL = "UPDATE citas_medicas
        SET CITAS_ESTADO = 'Finalizado'
        WHERE CONCAT(FECHA, ' ', (SELECT DATE_FORMAT(DATE_ADD(STR_TO_DATE(SUBSTRING_INDEX(NOM_HORA, 'a', -1), '%h:%i %p'), INTERVAL 1 MINUTE), '%h:%i %p') AS NewTime FROM horario WHERE HORA_ID = citas_medicas.HORA_ID)) <= NOW()
          AND CITAS_ESTADO NOT IN ('Anulada', 'Ausente');
        ";
        $items = $f3->DB->exec($cadenaSQL);
        
        if ($items) {
            echo json_encode([
                'mensaje' => 'Hay registro',
                
                'info' => ['items' => $items]
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'No existe información',
                'cantidad' => 0,
                'info' => ['items' => []]
            ]);
        }
        
    }
    
    public function crearEspecialidad($f3){
        $id = 0; // Variable para almacenar el ID del medico
        $msg = ""; // Variable para almacenar el mensaje de respuesta
       
        $especialidad = new M_especialidades();
        $especialidad->load(['ESP_ID = ?', $f3->get('POST.esp_id')]);
        if ($especialidad->loaded() > 0) {
            $msg = "Especialidad registrado.";
        } else {
            // Crear una nueva cita médica
            $newEsp = new M_especialidades();
            
            $newEsp->set('ESP_NOM', $f3->get('POST.esp_nom'));
            

            // Guardar la cita médica en la base de datos
            if ($newEsp->save()) {
                $id = $newEsp->get('ESP_ID');
                $msg = "Especialidad registrado con éxito.";
            } else {
                $msg = "Error al registrar la disponibilidad.";
            }
        }
    
        // Devolver la respuesta en formato JSON
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }
    public function eliminarEsp($f3) {
        $mensaje = "";
        $id = 0;
        $parametroId = $f3->get('PARAMS.id'); // se envía por la URL
    
        $this->M_especialidades->load(['ESP_ID=?', $parametroId]);
    
        if ($this->M_especialidades->loaded() > 0) {
            // Eliminar persona
            $this->M_especialidades->erase();
    
            $mensaje = "Especialidad eliminada con éxito";
            $id = 1;
        } else {
            $mensaje = "No existe la especialidad para eliminar";
        }
    
        echo json_encode([
            'mensaje' => $mensaje,
            'id' => $id
        ]);
    }
    public function actualizarEspecialidad($f3)
{
    $espID = $f3->get('PARAMS.id'); // Recuperando el ID de la especialidad que se quiere actualizar
    $this->M_especialidades->load(['ESP_ID=?', $espID]);
    $msg = "";
    $info = array();

    if ($this->M_especialidades->loaded() > 0) {
        // Proceso para actualizar la especialidad
        $especialidad = new M_especialidades();
        $especialidad->load(['ESP_ID = ?', $espID]);

        if ($especialidad->loaded() > 0) {
            $especialidad->set('ESP_NOM', $f3->get('POST.esp_nom'));
            $especialidad->save();

            $info['id'] = $especialidad->get('ESP_ID');
            $msg = "La especialidad se modificó con éxito";
        } else {
            $msg = "No se pudo modificar la especialidad";
            $info['id'] = 0;
        }
    } else {
        $msg = "La especialidad no existe";
        $info['id'] = 0;
    }
    
    echo json_encode([
        'mensaje' => $msg,
        'info' => $info
    ]);
}

    
}

?>
