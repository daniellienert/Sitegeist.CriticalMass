# Sitegeist.CriticalMass
### Automatic creation of node-hierarchies 

This package allows the configuration of a node hierarchies via eel configuration. 
A common use case would be to automatically create NewsCollection Nodes for Year and Month 
and move any News Node into a machig collection node.

## Authors & Sponsors

* Wilhelm Behncke - behncke@sitegeist.de
* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored 
by our employer http://www.sitegeist.de.*

## Usage

```yaml
Sitegeist:
  CriticalMass:
    automaticNodeHierarchy:
    
      # the configuration for the nodeType Sitegeist.CriticalMass:ExampleNode     
      'Sitegeist.CriticalMass:ExampleNode':
      
        # detect the root-collection node that will contain the automatic created node hierarchie
        root: "${q(node).parents().filter('[instanceof Sitegeist.CriticalMass:ExampleNodeCollection]').slice(-1, 1).get(0)}"
        
        # define the levels of the node hierarchie that is created inside the root node
        path:
          -
            name: "${'node-event-year-' + (q(node).property('startDate') ? Date.year(q(node).property('startDate')) : 'no-year')}"
            type: "${'Sitegeist.CriticalMass:ExampleNodeCollection'}"
            properties:
              title: "${q(node).property('startDate') ? Date.year(q(node).property('startDate')) : 'no-year'}"
              uriPathSegment: "${q(node).property('startDate') ? Date.year(q(node).property('startDate')) : 'no-year'}"
          -
            name: "${'node-event-month-' + (q(node).property('startDate') ? Date.month(q(node).property('startDate')) : 'no-month')}"
            type: "${'Sitegeist.CriticalMass:ExampleNodeCollection'}"
            properties:
              title: "${q(node).property('startDate') ? Date.month(q(node).property('startDate')) : 'no-month'}"
              uriPathSegment: "${q(node).property('startDate') ? Date.month(q(node).property('startDate')) : 'no-month'}"
```

## Limitations 

The following issues and side effects are known at the moment:

1. Currently there is no way to notify the navigate component about a 
   needed reload. So after a node was moved behind the scene the navigate 
   component will keep displaying the node on the current position until 
   the next reaload.
2. The automatically created nodes are in the user workspace and still 
   have to be published. It is possible that this will be changed in the future.



## Installation 

For now this package is not listed at packagist.org, so it needs to be configured via the `repositories` option in your composer.json.

```json
{
  "repositories": [
    {
      "url": "ssh://git@git.sitegeist.de:40022/sitegeist/Sitegeist.CriticalMass.git",
      "type": "vcs"
    }
  ]
}
```

You can then require it as a regular dependency:

```json
{
  "dependencies": {
    "sitegeist/criticalmass": "@dev"
  }
}
```

Currently `@dev` is recommended, since this package is still under development. Later on it should be replaced by the according semver string.

After you finished configuring your composer.json, run the following command to retrieve the package:

```shell
composer update sitegeist/criticalmass