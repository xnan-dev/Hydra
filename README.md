# Hydra
## Simple persistent data structures for small projects

The aim is solve the common demand for many php web projects to have some kind of
permanent state shared within an application which cannot be accomplished
very well using session object (either because implies an all-or-nothing heavy object
serialization or because the state is required to be shared between user session boundaries. 
Also, state might be needed to persist throughout reboots.

A usual approach to solve this problems is using a relational database but for rapid prototyping
or small project it adds a big overhead going back and forth between queries and relational structure
definitions. This library with some simple passive persistent data structures, all native, 
might offer a more confortable solution.

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

Currently,  these are the supported data structures:
- Matrix
- Map
- Object references

## Hydra properties
- Passive library
  - it does not require to setup any service or deamon, it runs only when called.
- Simple object storage 
  - object values assigned to persistent maps or references are stored by standard php serialization.
 - Thread safe storage
  - at storage time, objects are locked for exclusive write.
 - No setup policy
  - data structures are meant to adjust its properties according to usage, provide a reasonable default
  setting is considered a must.
  
## Data structures storage

All structures are stored in folder content/Hydra in php serialized form. 
For Matrix structure, a more compact binary storage is used.
