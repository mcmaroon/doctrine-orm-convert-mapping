<?php

/**
 * @Entity @Table(name="sample_entity")
 * @see http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#starting-with-the-product-entity
 **/
class SampleEntity
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     **/
    protected $id;
    /** 
     * @Column(type="string")
     **/
    protected $name;

    // .. (other code)
}