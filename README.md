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

## Using Maps
```php
$m=(Hydra\hydra())->maps()->retrieveOrCreateHMap(999,"testMap");

$m->set("w","hello");

if ($m->hasKey("w")) echo $m->get("w");  // echoes hello
```


## Using Matrixes
```php
$m=(Hydra\hydra())->matrixes()->retrieveOrCreateHMatrix(1,"testMatrix",[10,20]);		

$m->set([5,10],33.3);
$v=$m->get([5,10]); // 33.3
```

## Supported data structures
1. Matrix
2. Map
3. Object references

## Data structures storage:

All structures are stored in folder content/Hydra in php serialized form. 
For Matrix structure, a more compact binary storage is used.
