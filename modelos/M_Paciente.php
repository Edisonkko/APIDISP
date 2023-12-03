<?php
class M_Paciente extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'pacientes');
    }
}
?>