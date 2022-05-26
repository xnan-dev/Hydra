# Hydra
Simple Persistent data structures for PHP

##  Setting up the library
```php
use xnan\Trurl\Hydra;
Hydra\Functions::Load;

(Hydra\hydra())->hydrate();

// create or update your data structures.

(Hydra\hydra())->dehydrate(); // stores all new or changed structures.

```

## Using Matrix data structure
```php
// initial matrix setup
$m=(Hydra\hydra())->matrixes()->retrieveOrCreateHMatrix(1,"testMatrix",[10,20]);		

for ($b=0;$b<10;$b++) {
  for ($a=0;$a<10;$a++) {
    $v=($a+$b)*1.01;
    $m->set([$a,$b],$v);
    printf("set %s,%s:%s<br>",$a,$b,$v );
  }	
}

// reading matrix values (either stored or initially setup)
for ($b=0;$b<10;$b++) {
  for ($a=0;$a<20;$a++) {
    printf("value %s,%s: %s<br>",$a,$b,$m->get([$a,$b])); 			
  }
}
```
