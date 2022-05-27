<?php 
namespace xnan\Trurl\Hydra\Tests;
use xnan\Trurl\Hydra;
use PHPUnit\Framework\TestCase;


class HydraTest extends TestCase
{
    public function tesyHydrate()
    {
    	(Hydra\hydra())->hydrate();
    	(Hydra\hydra())->dehydrate();
  	}
    	
}

?>