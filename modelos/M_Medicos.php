<?php
class M_Medicos extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'medicos');
    }
}
?>