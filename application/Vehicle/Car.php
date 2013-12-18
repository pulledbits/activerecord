<?php

namespace App\Vehicle;

/**
 * Description of Car
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 * @behavior 
 */
class Car extends \App\Vehicle
{
    protected $speed = 0;
    
    /**
     * 
     * @param integer $speed desc rip tion
     * 
     * @behavior Accelrates
     *  Given $speed equals 0
     *  When $this->speed less than $speed
     *  Then $this->speed equals $speed
     */
    public function accelerateTo($speed)
    {
        $this->speed = $speed;
    }
          
    
    
}
