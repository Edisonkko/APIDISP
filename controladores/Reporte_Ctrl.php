<?php

class Reporte_Ctrl{
    public function __construct(){
        $this->M_citas = new M_Citas();
    }

    public function ReporteCitasAgendas($f3) {
        $fechaInicio = $f3->get('PARAMS.fechaInicio');
        $fechaFin = $f3->get('PARAMS.fechaFin');
        $cadenaSQL = "SELECT cm.*, p.PER_NOM AS nombre_paciente,p.PER_CED, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE cm.FECHA BETWEEN ? AND ?;        
                    ";
        $items = $f3->DB->exec($cadenaSQL,[$fechaInicio,$fechaFin]);
        
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

    public function ReporteCitasAgendasMedico($f3) {
        $fechaInicio = $f3->get('PARAMS.fechaInicio');
        $fechaFin = $f3->get('PARAMS.fechaFin');
        $medID = $f3->get('PARAMS.id');
        $cadenaSQL = "SELECT cm.*, p.PER_NOM AS nombre_paciente,p.PER_CED, m.PER_NOM AS nombre_medico, esp.ESP_NOM, h.NOM_HORA
        FROM citas_medicas cm
        INNER JOIN pacientes pc ON cm.PAC_ID = pc.PAC_ID
        INNER JOIN persona p ON pc.PER_ID = p.PER_ID
        INNER JOIN medicos mc ON cm.MED_ID = mc.MED_ID
        INNER JOIN persona m ON mc.PER_ID = m.PER_ID
        INNER JOIN especialidades esp ON mc.ESP_ID = esp.ESP_ID
        INNER JOIN horario h ON cm.HORA_ID = h.HORA_ID
        WHERE cm.FECHA BETWEEN ? AND ? AND mc.MED_ID=?;        
                    ";
        $items = $f3->DB->exec($cadenaSQL,[$fechaInicio,$fechaFin,$medID]);
        
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

   

   
    
   

   
  


}
?>