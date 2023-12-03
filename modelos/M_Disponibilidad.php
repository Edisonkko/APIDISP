<?php
class M_Disponibilidad extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'disponibilidad');
    }
}
?>