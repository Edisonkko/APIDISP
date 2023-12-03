<?php
class M_Citas extends \DB\SQL\Mapper{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'),'citas_medicas');
    }
}
?>