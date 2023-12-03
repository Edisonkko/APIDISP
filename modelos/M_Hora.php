<?php
class M_Hora extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'cita_hora');
    }
}
?>