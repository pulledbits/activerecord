<?php
/**
 * Description of Car
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 * @behavior 
 */
class Car
{
    protected $speed = 0;
    
    /**
     * 
     * @param integer $speed
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
