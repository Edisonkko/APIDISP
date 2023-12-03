<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

class Login_Ctrl{
    public $M_logins=null;
    public function __construct()
        {
            $this->M_logins=new M_Login();
        }

    public function validarUsr($f3){
       
        //obtener el usuario y la clave
        $usuario = $f3->get('POST.usuario');
        $clave = $f3->get('POST.clave');
        
        $msg = "";
        $cadenaSql='';
        /* $cadenaSql=$cadenaSql.' SELECT u.USR_ID, u.ROL_ID, u.USR_CORREO, r.ROL_ID, r.ROL_DESC ';
        $cadenaSql=$cadenaSql.' FROM usuarios u INNER JOIN roles r ON u.ROL_ID = r.ROL_ID';
        $cadenaSql=$cadenaSql. ' where ';
        $cadenaSql=$cadenaSql. ' u.USR_CORREO='."'". $usuario."'" ;
        $cadenaSql=$cadenaSql. ' AND u.USR_PASS= '."'".$clave."'"; */

        $cadenaSql = $cadenaSql . ' SELECT u.USR_ID, u.ROL_ID, u.USR_CORREO, r.ROL_ID, r.ROL_DESC, p.PER_ID, p.PER_NOM, ';
        $cadenaSql = $cadenaSql . ' CASE ';
        $cadenaSql = $cadenaSql . " WHEN r.ROL_DESC = 'Paciente' THEN pa.PAC_ID ";
        $cadenaSql = $cadenaSql . " WHEN r.ROL_DESC = 'Medico' THEN m.MED_ID ";
        $cadenaSql = $cadenaSql . ' END AS ID_ESPECIFICO ';
        $cadenaSql = $cadenaSql . ' FROM usuarios u INNER JOIN roles r ON u.ROL_ID = r.ROL_ID ';
        $cadenaSql = $cadenaSql . ' INNER JOIN persona p ON p.USR_ID = u.USR_ID ';
        $cadenaSql = $cadenaSql . ' LEFT JOIN pacientes pa ON pa.PER_ID = p.PER_ID ';
        $cadenaSql = $cadenaSql . ' LEFT JOIN medicos m ON m.PER_ID = p.PER_ID ';
        $cadenaSql = $cadenaSql . " WHERE u.USR_CORREO = '" . $usuario . "' ";
        $cadenaSql = $cadenaSql . " AND u.USR_PASS = '" . $clave . "'";

        $items=$f3->DB->exec($cadenaSql);
        echo json_encode([
        'mensaje'=>count($items)>0? 'Acceso permitido':'Usuario o clave Incorrecta',
        'info' => ['items' => $items]
        ]);
    }

    public function menu($f3){
        $parametroPerfil=$f3->get('POST.perfil');
        $cadenaSQL='';
        $cadenaSQL=$cadenaSQL.'  SELECT m.NOM_MENU, m.PAGINA, r.ROL_ID';
        $cadenaSQL=$cadenaSQL.' FROM roles r INNER JOIN rol_menu a ON r.ROL_ID = a.ROL_ID';
        $cadenaSQL=$cadenaSQL.' INNER JOIN menu m ON a.ID_MENU = m.ID_MENU';
        $cadenaSQL=$cadenaSQL.' WHERE';
        $cadenaSQL=$cadenaSQL.' r.ROL_ID='.$parametroPerfil;
        //echo $cadenaSQL;

        
        $items=$f3->DB->exec($cadenaSQL);
        echo json_encode(
            $items
        );
        /*echo json_encode([
        'mensaje'=>count($items)>0? 'Hay registro':'no existe menu para el perfil',
        'cantidad'=>count($items),
        'info'=>[
            'items'=>$items
           
        ]
        ]); */
    }
   
    public function recuperarContra($f3) {
        date_default_timezone_set('America/Bogota');
        $parametroCorreo = $f3->get('POST.correo');
        $existeCorreo = $this->verificarExistenciaDeCorreo($parametroCorreo, $f3);
    
        if ($existeCorreo) {
            try {
                // Generar un token único y una fecha de expiración
                $token = bin2hex(random_bytes(32)); // Genera un token seguro de 64 caracteres
                $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expira en 1 hora
    
                // Obtener el ID del usuario
                $sqlIdUsuario = 'SELECT USR_ID FROM usuarios WHERE USR_CORREO = ?';
                $stmtIdUsuario = $f3->DB->exec($sqlIdUsuario, [$parametroCorreo]);
                $idUsuario = $stmtIdUsuario[0]['USR_ID'];

                // Almacena el token y la expiración en la base de datos junto con el correo electrónico
                $sql = 'INSERT INTO recuperacion_contrasena (USR_ID, token, expiracion) 
                SELECT u.USR_ID, ?, ? 
                FROM usuarios u 
                WHERE u.USR_CORREO = ?';
                $stmt = $f3->DB->exec($sql, [$token, $expiracion, $parametroCorreo]);
                // Elimina tokens expirados
                $this->eliminarTokensExpirados($f3);
                if ($stmt) {
                    // Envia el correo electrónico al usuario
                    $enlace = 'http://localhost:4200/recuperarcontra?id=' . $idUsuario . '&token=' . $token;
                    $mail = new PHPMailer();
                    // Configura las propiedades del correo electrónico
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Reemplaza con el servidor SMTP que vayas a usar
                    $mail->SMTPAuth = true;
                    $mail->Username = 'ecacaoe@gmail.com'; // Reemplaza con tu dirección de correo electrónico
                    $mail->Password = 'vktgcocgqsbzjysf'; // Reemplaza con tu contraseña
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('tu_correo@example.com', 'Dispensario Seguro Campesino');
                    $mail->addAddress($parametroCorreo, 'Destinatario');
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperacion de Clave';
                    $mail->Body = 'Se ha solicitado una recuperación de contraseña. Puedes cambiar tu contraseña en el siguiente enlace: <a href="' . $enlace . '">' . $enlace . '</a>';

                    // Envía el correo
                    $mail->send();
                     echo json_encode(['success' => true, 'token' => $token]);
                } else {
                    echo json_encode(['success' => false]);
                }
            } catch (Exception $e) {
                // Manejo de errores
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            // El correo no existe en la base de datos, devolver un mensaje de error
            $response = [
                'success' => false,
                'message' => 'La dirección de correo no existe en nuestra base de datos.'
            ];
            echo json_encode($response);
        }
    }
    
    public function enviarCorreoConfirmacionCita($f3) {
        
        date_default_timezone_set('America/Bogota');
        // Obteniendo el correo y detalles de la cita del objeto $f3
        $correoDestino = $f3->get('POST.correoDestino');
        $detallesCita = $f3->get('POST.detallesCita');
        $existeCorreo = $this->verificarExistenciaDeCorreo($correoDestino, $f3);
        if ($existeCorreo) {
            $mail = new PHPMailer();
            // Configurar las propiedades del correo electrónico
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Reemplaza con el servidor SMTP que vayas a usar
            $mail->SMTPAuth = true;
            $mail->Username = 'ecacaoe@gmail.com'; // Reemplaza con tu dirección de correo electrónico
            $mail->Password = 'vktgcocgqsbzjysf'; // Reemplaza con tu contraseña
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
        
            $mail->setFrom('tu_correo@example.com', 'Dispensario Seguro Campesino');
            $mail->addAddress($correoDestino, 'Destinatario');
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de Cita';
        
            // Construir el cuerpo del mensaje con los detalles de la cita
        
            $mail->Body = $detallesCita;

            // Envía el correo
            if ($mail->send()) {
                echo json_encode(['success' => true, 'message' => 'Correo de confirmación enviado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
            }
        }else {
            // El correo no existe en la base de datos, devolver un mensaje de error
            $response = [
                'success' => false,
                'message' => 'La dirección de correo no existe en nuestra base de datos.'
            ];
            echo json_encode($response);
        }
        
    }  
    

    private function verificarExistenciaDeCorreo($correo, $f3) {
        $cadenaSQL = "SELECT us.USR_CORREO FROM usuarios as us WHERE us.USR_CORREO = :correo";
        $consulta = $f3->DB->exec($cadenaSQL, [':correo' => $correo]);
        return count($consulta) > 0;
    }
    private function eliminarTokensExpirados($f3) {
        // Consulta para eliminar tokens expirados
        $sql = 'DELETE FROM recuperacion_contrasena WHERE expiracion < NOW()';
        $f3->DB->exec($sql);
    }
    public function cambiarContra($f3) {
        $nuevaClave = $f3->get('POST.nueva_clave'); // Cambia POST.nueva_clave al nombre correcto
        $token = $f3->get('POST.token'); // Cambia POST.token al nombre correcto
        
        // Obtener la contraseña anterior del usuario usando el token
        $consultaClaveAnterior = '
        SELECT u.USR_PASS
        FROM usuarios u
        INNER JOIN recuperacion_contrasena rc ON u.USR_ID = rc.USR_ID
        WHERE rc.token = ?
        AND rc.expiracion > NOW()
        ';

        try {
                $claveAnterior = $f3->DB->exec($consultaClaveAnterior, [$token]);

            if ($claveAnterior[0]['USR_PASS'] === $nuevaClave) {
                echo json_encode(['success' => false, 'message' => 'La nueva contraseña no puede ser igual a la contraseña anterior']);
                return;
            }

            $cadenaSQL = '
            UPDATE usuarios
            SET USR_PASS = ?
            WHERE USR_ID IN (
                SELECT u.USR_ID
                FROM usuarios u
                INNER JOIN recuperacion_contrasena rc ON u.USR_ID = rc.USR_ID
                WHERE rc.token = ?
                AND rc.expiracion > NOW()
            )
            ';
            $result = $f3->DB->exec($cadenaSQL, [$nuevaClave, $token]);
    
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Contraseña actualizada con éxito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'El token ya esta caducado',$claveAnterior]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        
    
        
    }
    
    
    
    
    

   
  


}
?>