<?php
class Registro_Ctrl{
    
    
    public function __construct()
    {
        $this->M_usuario=new M_Usuario();
        $this->M_persona=new M_Persona();
        $this->M_medicos=new M_Medicos();
        $this->M_paciente=new M_Paciente();
        $this->M_disponibilidad=new M_Disponibilidad();
        $this->M_especialidades=new M_Especialidades();
    }

    public function registrarPaciente($f3){
        $id = 0;
        $msg = "";
    
        // Verificar si el paciente ya existe por número de cédula
       
        $paciente = new M_persona();
        $paciente->load(['PER_CED = ?', $f3->get('POST.per_ced')]);
    
        if ($paciente->loaded() > 0) {
            $msg = "El paciente con la cédula proporcionada ya está registrado.";
        } else {
            // Verificar si el usuario ya existe por correo electrónico
            $usuarioExistente = new M_usuario();
            $usuarioExistente->load(['USR_CORREO = ?', $f3->get('POST.usr_correo')]);
    
            if ($usuarioExistente->loaded() > 0) {
                $msg = "El usuario con el correo electrónico proporcionado ya está registrado.";
            } else {
                // Crear un nuevo usuario
                $usuario = new M_usuario();
                $usuario->set('USR_CORREO', $f3->get('POST.usr_correo'));
                $usuario->set('USR_PASS', $f3->get('POST.usr_pass'));
                $usuario->set('ROL_ID', 2); // ID del rol de paciente (asumiendo que 2 es el ID correspondiente)
    
                // Guardar el usuario en la base de datos
                $usuario->save();
    
                // Obtener el ID del usuario creado
                $usr_id = $usuario->get('USR_ID');
    
                // Crear una nueva persona asociada al usuario
                $persona = new M_persona();
                $persona->set('USR_ID', $usr_id);
                $persona->set('PER_CED', $f3->get('POST.per_ced'));
                $persona->set('PER_NOM', $f3->get('POST.per_nom'));
                $persona->set('PER_APEL', $f3->get('POST.per_apellido'));
                $persona->set('PER_TEL', $f3->get('POST.per_tel'));
                $persona->set('PER_ESTADO', 'a'); // Estado predeterminado (puedes ajustarlo según tus necesidades)
    
                // Guardar la persona en la base de datos
                $persona->save();
    
                // Obtener el ID de la persona creada
                $per_id = $persona->get('PER_ID');
    
                // Crear un nuevo registro de paciente asociado a la persona
                $nuevoPaciente = new M_paciente();
                $nuevoPaciente->set('PER_ID', $per_id);
    
                // Guardar el registro de paciente en la base de datos
                $nuevoPaciente->save();
    
                $id = $nuevoPaciente->get('PAC_ID');
                $msg = "Paciente registrado con éxito.";
            }
        }
    
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }

    public function crearAdministrator($f3) {
        $id = 0;
        $msg = "";
    
        // Verificar si el paciente ya existe por número de cédula
       
        $paciente = new M_persona();
        $paciente->load(['PER_CED = ?', $f3->get('POST.per_ced')]);
    
        if ($paciente->loaded() > 0) {
            $msg = "La persona con la cédula proporcionada ya está registrado.";
        } else {
            // Verificar si el usuario ya existe por correo electrónico
            $usuarioExistente = new M_usuario();
            $usuarioExistente->load(['USR_CORREO = ?', $f3->get('POST.usr_correo')]);
    
            if ($usuarioExistente->loaded() > 0) {
                $msg = "El usuario con el correo electrónico proporcionado ya está registrado.";
            } else {
                // Crear un nuevo usuario
                $usuario = new M_usuario();
                $usuario->set('USR_CORREO', $f3->get('POST.usr_correo'));
                $usuario->set('USR_PASS', $f3->get('POST.usr_pass'));
                $usuario->set('ROL_ID', 1); // ID del rol de paciente (asumiendo que 2 es el ID correspondiente)
    
                // Guardar el usuario en la base de datos
                $usuario->save();
    
                // Obtener el ID del usuario creado
                $usr_id = $usuario->get('USR_ID');
    
                // Crear una nueva persona asociada al usuario
                $persona = new M_persona();
                $persona->set('USR_ID', $usr_id);
                $persona->set('PER_CED', $f3->get('POST.per_ced'));
                $persona->set('PER_NOM', $f3->get('POST.per_nom'));
                $persona->set('PER_APEL', $f3->get('POST.per_apellido'));
                $persona->set('PER_TEL', $f3->get('POST.per_tel'));
                $persona->set('PER_ESTADO', 'activo'); // Estado predeterminado (puedes ajustarlo según tus necesidades)
    
                // Guardar la persona en la base de datos
                $persona->save();
    
                // Obtener el ID de la persona creada
                $id = $persona->get('PER_ID');
                $msg = "Administrador registrado con éxito.";
            }
        }
    
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }

    public function crearMedico($f3) {
        $id = 0;
        $msg = "";
    
        // Verificar si el paciente ya existe por número de cédula
       
        $paciente = new M_persona();
        $paciente->load(['PER_CED = ?', $f3->get('POST.per_ced')]);
    
        if ($paciente->loaded() > 0) {
            $msg = "El paciente con la cédula proporcionada ya está registrado.";
        } else {
            // Verificar si el usuario ya existe por correo electrónico
            $usuarioExistente = new M_usuario();
            $usuarioExistente->load(['USR_CORREO = ?', $f3->get('POST.usr_correo')]);
    
            if ($usuarioExistente->loaded() > 0) {
                $msg = "El usuario con el correo electrónico proporcionado ya está registrado.";
            } else {
                // Crear un nuevo usuario
                $usuario = new M_usuario();
                $usuario->set('USR_CORREO', $f3->get('POST.usr_correo'));
                $usuario->set('USR_PASS', $f3->get('POST.usr_pass'));
                $usuario->set('ROL_ID', 3); // ID del rol de paciente (asumiendo que 2 es el ID correspondiente)
    
                // Guardar el usuario en la base de datos
                $usuario->save();
    
                // Obtener el ID del usuario creado
                $usr_id = $usuario->get('USR_ID');
    
                // Crear una nueva persona asociada al usuario
                $persona = new M_persona();
                $persona->set('USR_ID', $usr_id);
                $persona->set('PER_CED', $f3->get('POST.per_ced'));
                $persona->set('PER_NOM', $f3->get('POST.per_nom'));
                $persona->set('PER_APEL', $f3->get('POST.per_apellido'));
                $persona->set('PER_TEL', $f3->get('POST.per_tel'));
                $persona->set('PER_ESTADO', 'activo'); // Estado predeterminado (puedes ajustarlo según tus necesidades)
    
                // Guardar la persona en la base de datos
                $persona->save();

                 // Obtener el ID de la persona creada
                 $id = $persona->get('PER_ID');
                 // Crear un nuevo registro del medico asociado a la persona
                 $nuevoMedico = new M_medicos();
                 $nuevoMedico->set('PER_ID', $id);
                 $nuevoMedico->set('ESP_ID', $f3->get('POST.esp_id'));

     
                 // Guardar el registro de paciente en la base de datos
                 $nuevoMedico->save();


                 $msg = "Médico registrado con éxito.";


    
            }
        }
    
        echo json_encode([
            'mensaje' => $msg,
            'info' => ['id' => $id]
        ]);
    }


    public function ValidarCedula($f3) {
        $ced = 0;
        $paciente = new M_persona();
        $paciente->load(['PER_CED = ?', $f3->get('POST.per_ced')]);
    
        if ($paciente->loaded() > 0) {
            $ced = $paciente->get('PER_CED');
        }
        echo json_encode([
            'info' => ['cedula' => $ced]
        ]);
    }
    
    public function ValidarCorreo($f3) {
        $correo = '';
        $paciente = new M_usuario();
        $paciente->load(['USR_CORREO = ?', $f3->get('POST.usr_correo')]);
    
        if ($paciente->loaded() > 0) {
            $correo = $paciente->get('USR_CORREO');
        } 
        echo json_encode([
            'info' => ['correo' => $correo]
        ]);
    }
    
    
    
    
}
?>