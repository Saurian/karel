<?php


namespace CmsModule\Entities;


trait TagsTrait
{

    private $default_tag = null;

    private static $tags = [
        'tagColor1' => 'tagColor1',
        'tagColor2' => 'tagColor2',
        'tagColor3' => 'tagColor3',
        'tagColor4' => 'tagColor4',
        'tagColor5' => 'tagColor5',
        'tagColor6' => 'tagColor6',
        'tagColor7' => 'tagColor7',
    ];


    /**
     * @var string
     * @ORM\Column(type="string", length=32, options={"comment":"štítek"}, nullable=true)
     */
    protected $tag;



    /**
     * @return array
     */
    public static function getTags()
    {
        return self::$tags;
    }


    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = in_array($tag, self::$tags) ? $tag : $this->default_tag;
        return $this;
    }


}