<?php
class M_Persona extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'persona');
    }
}
?>