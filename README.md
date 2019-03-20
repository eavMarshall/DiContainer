# DiContainer

Super simple dependency injection container for php 5.6

## Performance test DiContainer vs Dice
Dice is super fast, doing some test against it seem like a good idea (https://github.com/Level-2/Dice/tree/v2.0-PHP5.4-5.5)
### Create class A 10000 times
Dice 			0.0078229904174805

DiContainer 	0.0034599304199219

### Create class J 10000 times
Dice 			0.21785092353821

DiContainer 	0.12657999992371

### Create class SessionInfo as a singleton and inject it into new instance of ClassHoldingSessionInfoIsUpdated 10000 times
Dice 			0.026506900787354

DiContainer 	0.019238948822021

### Create instance 3 level deep x2 each layer 10000 times
Dice 			0.11162400245667

DiContainer 	0.079232931137085

### Inject itself into class 10000 times
Dice 			0.024734973907471

DiContainer 	0.0049588680267334
