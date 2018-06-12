<?php

class Signature extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(column="user", type="integer", length=11, nullable=false)
     */
    public $user;

    /**
     *
     * @var integer
     * @Primary
     * @Column(column="audio", type="integer", length=11, nullable=false)
     */
    public $audio;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("mp3");
        $this->setSource("signature");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'signature';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Signature[]|Signature|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Signature|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
