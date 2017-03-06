<?php

namespace Toro\Bundle\GeoBundle\Model;

class ZoneMember implements ZoneMemberInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var ZoneInterface
     */
    protected $belongsTo;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getBelongsTo()
    {
        return $this->belongsTo;
    }

    /**
     * {@inheritdoc}
     */
    public function setBelongsTo(ZoneInterface $belongsTo = null)
    {
        $this->belongsTo = $belongsTo;
    }
}
