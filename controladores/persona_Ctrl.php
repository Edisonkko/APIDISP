<?php
class persona_Ctrl{
    public $M_persona=null;
    public function __construct()
        {
            $this->M_persona=new M_Persona();
            $this->M_usuario=new M_Usuario();
        }

    public function getListarPersona($f3){
        $cadenaSQL='SELECT persona.*, usuarios.USR_CORREO,usuarios.USR_PASS, roles.ROL_DESC, pacientes.PAC_ID
        FROM persona
        INNER JOIN usuarios ON persona.USR_ID = usuarios.USR_ID
        INNER JOIN roles ON usuarios.ROL_ID = roles.ROL_ID
        INNER JOIN pacientes ON persona.PER_ID = pacientes.PER_ID;';
        $items=$f3->DB->exec($cadenaSQL);
        echo json_encode([
        'mensaje'=>count($items)>0? 'Hay registro':'No existe informacion',
        'cantidad'=>count($items),
        'info'=>[
            'items'=>$items
           
        ]
        ]);
    }

    public function listarPersonaxID($f3) {
        $parametroID = $f3->get('PARAMS.id');
        $cadenaSQL = 'SELECT p.*, u.* FROM persona p
                      INNER JOIN usuarios u ON p.USR_ID = u.USR_ID
                      WHERE p.PER_ID = ?';
        $items = $f3->DB->exec($cadenaSQL, $parametroID);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }


    public function actualizarPersona($f3) {
        $personaID = $f3->get('PARAMS.id'); // Recuperando el ID de la persona que se quiere actualizar
        $this->M_persona->load(['PER_ID=?', $personaID]);
        $msg = "";
        $info = array();
    
        if ($this->M_persona->loaded() > 0) {
            // Proceso para actualizar
            $persona = new M_persona();
            $persona->load(['PER_ID = ? ', $personaID]);
    
            if ($persona->loaded() > 0 && $persona->loaded() < 2) {
                $this->M_persona->set('PER_CED', $f3->get('POST.cedula'));
                $this->M_persona->set('PER_NOM', $f3->get('POST.nombres'));
                $this->M_persona->set('PER_APEL', $f3->get('POST.apellidos'));
                $this->M_persona->set('PER_TEL', $f3->get('POST.telefono'));
                $this->M_persona->set('PER_ESTADO', $f3->get('POST.estado'));
                $this->M_persona->save();
    
                // Actualizar tabla de usuarios
                $usuarioID = $this->M_persona->get('USR_ID');
                $usuario = new M_usuario();
                $usuario->load(['USR_ID=?', $usuarioID]);
                if ($usuario->loaded() > 0) {
                    $usuario->set('USR_CORREO', $f3->get('POST.usuario_nombre'));
                    $usuario->set('USR_PASS', $f3->get('POST.usuario_clave'));
                    $usuario->save();
                }
    
                $info['id'] = $this->M_persona->get('PER_ID');
                $msg = "se modificó con éxito";
            } else {
                $msg = "El registro no se pudo modificar";
                $info['id'] = 0;
            }
        } else {
            $msg = "El registro no existe";
            $info['id'] = 0;
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }
    
    public function eliminar($f3) {
        $mensaje = "";
        $id = 0;
        $parametroId = $f3->get('PARAMS.id'); // se envía por la URL
    
        $this->M_persona->load(['PER_ID=?', $parametroId]);
    
        if ($this->M_persona->loaded() > 0) {
            // Eliminar persona
            $this->M_persona->erase();
    
            // Eliminar usuario asociado
            $usuarioID = $this->M_persona->get('USR_ID');
            $usuario = new M_usuario();
            $usuario->load(['USR_ID=?', $usuarioID]);
            if ($usuario->loaded() > 0) {
                $usuario->erase();
            }
    
            $mensaje = "Persona eliminada con éxito";
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
    
    public function listarPersonaxCed($f3) {
        $parametroID = $f3->get('PARAMS.ced');
        $cadenaSQL = 'SELECT p.*, pa.* FROM persona p
                      INNER JOIN pacientes pa ON p.PER_ID = pa.PER_ID
                      WHERE p.PER_CED = ?';
        $items = $f3->DB->exec($cadenaSQL, $parametroID);
    
        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Hay Registros' : 'No existe información',
            'cantidad' => count($items),
            'info' => [
                'items' => $items     
            ]
        ]);
    }
}
?>