<?php

class Audio extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     * @Primary
     * @Column(column="id", type="string", length=100, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(column="name", type="string", length=100, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(column="url", type="string", nullable=false)
     */
    public $url;

    /**
     *
     * @var string
     * @Column(column="type", type="string", length=10, nullable=false)
     */
    public $type;

    /**
     *
     * @var string
     * @Column(column="signature", type="string", length=100, nullable=false)
     */
    public $signature;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("mp3");
        $this->setSource("audio");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'audio';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Audio[]|Audio|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Audio|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
