# Mesosfer Library
This library for make to easy use mesosfer database, this package available to crud method.

## Install
```composer require aacassandra/mesosfer-lib```
or
Visit [composer](https://packagist.org/packages/aacassandra/mesosfer-lib)

## Usage
This library available 5 options
- getObject
- getAllObject
- storeObject
- updateObject
- deleteObject

### getObject
```
use Mesosfer\MesosferLib;

class ExamplesController extends Controller
{
  public function index(Request $request, $className, $id)
  {
      $options = [
        "include" => [
          'floor'
        ]
      ];

      $mesosfer = MesosferLib::getObject($className, $id, $options);
      if ($mesosfer->status) {
          $data = $mesosfer->output;
          return view($this->indexView, compact('data'));
      } else {
          //If server down
          $data = [];
          return view($this->indexView, compact('data'));
      }
  }  
}
```
 ### getAllObject
 ```
use Mesosfer\MesosferLib;

class ExamplesController extends Controller
{
  public function index(Request $request, $className)
  {
      $options = [
        "include" => [
          'floor'
        ]
      ];

      $mesosfer = MesosferLib::getAllObject($className, $options);
      if ($mesosfer->status) {
          $data = $mesosfer->output;
      } else {
          //If server down
      }
  }  
}
 ```

 ### storeObject
  ```
use Mesosfer\MesosferLib;

class ExamplesController extends Controller
{
  public function create(Request $request, $className)
  {
        $data = [
          ['pointer','floor',$request->adFloor,'Floor'],
          ['array','videoAds',$request->adVideo],
          ['array','smallAds',$request->adSmall],
          ['string','mediumAds',$request->adMedium,],
          ['array','bigAds',$request->adBig]
        ];

        $mesosfer = MesosferLib::storeObject($className, $data);
        if ($mesosfer->status) {
            // If Success
        } else {
            // If failed
        }
  }  
}
 ```

### updateObject
 ```
 use Mesosfer\MesosferLib;

class ExamplesController extends Controller
{
  public function update(Request $request, $className, $id)
  {
        $data = [
          ['pointer','floor',$request->adFloor,'Floor'],
          ['array','videoAds',$request->adVideo],
          ['array','smallAds',$request->adSmall],
          ['string','mediumAds',$request->adMedium,],
          ['array','bigAds',$request->adBig]
        ];

        $mesosfer = MesosferLib::updateObject($className, $id, $data);
        if ($mesosfer->status) {
            // If Success
        } else {
            // If failed
        }
  }  
}
 ```

### deleteObject
 ```
 use Mesosfer\MesosferLib;

class ExamplesController extends Controller
{
  public function update(Request $request, $className, $id)
  {
        $mesosfer = MesosferLib::deleteObject($className, $id);
        if ($mesosfer->status) {
            // If Success
        } else {
            // If failed
        }
  }  
}
 ```

### Available Data Format
Will work only if used of storeObject and updateObject functions
##### data
- pointer
- string
- date
- number
- boolean
- array
- object
- image
- geopoint

```
$data = [
  ['pointer','floor',$request->adFloor,'Floor'],
  ['array','videoAds',$request->adVideo],
  ['array','smallAds',$request->adSmall],
  ['string','mediumAds',$request->adMedium,],
  ['array','bigAds',$request->adBig],
  ['date','end',new DateTime($clientRequest->end)],
  ['number','numberOfParticipants',$clientRequest->numberOfParticipants],
  ['boolean','withVideoConference',$clientRequest->videoConference],
  ['image','photo1',$request->file('photo0')],
  ['geopoint','location',[-1.9924,23.1233]]
];
```

### Available Options
Will work only if used of getObject and getAllObject functions
##### Include
 ```
 $options = [
    "include" => [
      'floor','floor.room'
    ]
  ];
```

##### where
- equalTo
- equalToNumber
- notEqualTo
- notEqualToNumber
- containedIn
- notContainedIn
- greaterThan
- lessThan
- greaterThanOrEqualTo
- lessThanOrEqualTo
- greaterThanRelativeTime
- lessThanRelativeTime
- greaterThanOrEqualToRelativeTime
- lessThanOrEqualToRelativeTime

 ```
  $options1 = [
    "include" => [
      'floor'
    ],
    "where" => [
      [
        "object" => "capacity",
        "greaterThanOrEqualTo" => $clientRequest->numberOfParticipants
      ],
      [
        "object" => "withVideoConference",
        "equalTo" => $clientRequest->videoConference
      ],
      [
        "object" => "allowGuests",
        "equalTo" => $clientRequest->allowGuests
      ]
    ]
  ];
```

## License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/aacassandra/mesosfer-lib/blob/master/LICENSE) file for details
