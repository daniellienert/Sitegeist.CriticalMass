# Sitegeist.CriticalMass
## Tools for managing large amounts of nodes

### Automatic creation of node-hierarchies
 
In Neos it is sometimes hard to handle high amounts of documents below a 
single parent node. In such cases it is hard for editors to find a 
specific document again. On top of that the performance and usability of 
the current navigate-component will suffer if too many nodes are on a 
single level.

To overcome this we suggest to use a hierarchical structure of 
collection-nodes to create a node structure that is appropriate 
for the current project and that helps editors to find documents. 
This solution has the additional benefit of representing that structure 
in the document-url aswell, that makes url-collisions much less likely.

*Since the creation of such a node hierarchy is a repetitive task we 
provide this package to help automating that.*

A common use case would be to automatically create NewsCollection Nodes for Year and Month 
and move any News Node into a matchig collection node.

### Importing of nodes from CSV-Files

It is a repeated requirement to import data from table-structures into neos that usually requires the 
implementation of custom import controllers. This package allows to configure the import into nodes or even
into node hierarchies.

NOTE: The import works together with the automatic node-hierarchy creation. So if you want to import into 
a structure you can configure both options.

### Exporting of nodes to CSV-Files

For exporting nodes into a table-structure the package allow the configuration of the expression to query for nodes and
expressions for each column of the imported table.

## Authors & Sponsors

* Wilhelm Behncke - behncke@sitegeist.de
* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored 
by our employer http://www.sitegeist.de.*

## Usage

```yaml
Sitegeist:
  CriticalMass:
  
    # 
    # Automatic hierarchy creation
    # 
    automaticNodeHierarchy:
    
      # The configuration for the node type Sitegeist.CriticalMass:ExampleNode     
      'Sitegeist.CriticalMass:ExampleNode':

        # optional: Eel-values that are evaluates first and afterwards are available in the context 
        # of the eel expressions below. During evaluation the `site`, `documentNode` and `node` values are present.
        context: 
          year: "${q(node).property('startDate') ? Date.year(q(node).property('startDate')) : 'no-year'}"
          month: "${q(node).property('startDate') ? Date.month(q(node).property('startDate')) : 'no-month'}"

        # Detect the root-collection node that will contain the automatically created node hierarchy
        root: "${q(node).parents().filter('[instanceof Sitegeist.CriticalMass:ExampleNodeCollection]').slice(-1, 1).get(0)}"
        
        # optional: Automatically publish the created document hierarchy
        autoPublishPath: true

        # optional: Decide wether a node shall be handled or not
        condition: "${q(node).property('startDate') ? true : false}"
        
        # optional: The sorting of the nodes inside the target hierarchy
        sortBy: '${q(a).property("title") < q(b).property("title")}'

        # Define the levels of the node hierarchy that are created beneath the root node
        path:
       
          # level 1 year
          -
            # the type of the hierarchy-node  
            type: 'Sitegeist.CriticalMass:ExampleNodeCollection'
            
            # eel-expression to find existing collection nodes
            # the context contains the parent node as `parent` in addition to the default context values
            node: "${q(parent).children('[uriPathSegment = '\' + year + ' '\]').get(0)}"
            
            # optional: the sorting of the nodes on this level
            # the eel expression gets two nodes `a` and `b` in the context
            sortBy: '${q(a).property("title") < q(b).property("title")}'
             
            # properties that are applied only on node creation and can be edited afterwards
            properties:
              title: "${year}"
              uriPathSegment: "${year}"
          
          # level 2 month
          -
            # the type and nodename of the hierarchy-node  
            type: 'Sitegeist.CriticalMass:ExampleNodeCollection'
            # eel-expression to find existing collection nodes
            # the context contains the parent node as `parent` in addition to the default context values
            node: "${q(parent).children('[uriPathSegment = '\' + month + ' '\]').get(0)}"
            
            # optional: the sorting of the nodes on this level
            # the eel expression gets two nodes `a` and `b` in the context
            sortBy: '${q(a).property("title") < q(b).property("title")}'
             
            # properties that are applied only on node creation and can be edited afterwards
            properties:
              title: "${month}"
              uriPathSegment: "${month}"
    
    #
    # Node-import
    #
    import:
    
      # A single import preset for Sitegeist.CriticalMass:ExampleNode
      # 
      # !!! All expressions in here are evaluated with the `site` and the current `row` in the context.
      example-import:
      
        # optional: Description for the preset
        description: "Example-import description"
        
        # optional Eel-values that are evaluates first and afterwards are available in the context 
        context: 
          base: "${q(site).find('[instanceof Sitegeist.CriticalMass:ExampleCollection]').get(0)}"
        
        # optional: Configuration for importing previously imported nodes
        update: 
          # Expression that returns the node that shall be updated 
          node: "${q(base).find('[instanceof Sitegeist.CriticalMass:ExampleNode][importIdentifier=\"' +  row['ID'] + '\"]').get(0)}"

        # optional: Configuration for creating new nodes if no update was configured or no preexisting node is found
        create:
          # optional: skip import under certain conditions
          condition: "${row['ID'] ? true : false}"
          # Expression that returns the node that shall be updated 
          parentNode: "${base}"   
          # The type of the node that shall be created
          type: 'Sitegeist.CriticalMass:ExampleNode'
          # optional:  The properties that are set once for new imported nodes  
          properties:
            'importIdentifier': "${row['ID']}"

        # The properties that are updated during import AND update
        properties:
          'title': "${row['Title']}"
          'subtitle': "${row['Subtitle']}"
          'description': "${row['Description']}"
          'date': "${Date.parse(row['Date'], 'y-m-d')}"

        # optional: Import data into descendent-nodes
        #
        # !!! The expressions in here have the imported or updated node in the context as `ancestor`
        #
        # All the configuration from the main level can be used in here aswell. Descendent-nodes can
        # even have their own descendent nodes.
        descendants:
          image:
            update:
              node: "${q(ancestor).children('images').children().get(0)}"
            create:
              condition: "${row['Image'] ? true : false}"
              parentNode: "${q(ancestor).children('images').get(0)}"
              nodeType: 'Sitegeist.CriticalMass:ExampleImage'
            properties:
              'title': "${row['Title']}"
              'image': "${row['Image']}"
              
    #
    # Node-export
    #
    export:
      
      # A single export preset for Sitegeist.CriticalMass:ExampleNode
      # 
      # !!! All expressions in here are evaluated with the `site` and the current `row` in the context.
      example-export:
      
        # optional: Description for the preset
        description: "Example-export description"

        # Expression that returns the nodes that shall be exported 
        nodes: "${q(site).find('[instanceof Sitegeist.CriticalMass:ExampleNode]').get()}"
        
        # The properties that are exported
        #
        # Each configuration key in here is evaluated as en expression with 'site' and 'node' in the context.
        properties:
          'ID': "${q(node).property('importIdentifier')}"
          'Title': "${q(node).property('title')}"
          'Subtitle': "${q(node).property('subtitle')}"
          'Date': "${Date.format(q(node).property('date'), 'y-m-d')}"
          'Image': "${q(node).children('images').children().first().property('image')}"
```

## Commands

- `./flow csv:showpresets` - List the defined import and export presets
- `./flow csv:import <preset> <file>` - Import or update nodes from csv-file
- `./flow csv:export <preset> <file>` - Export nodes to csv-file

*The import- and export-commands are expecting the field-names in the first line of the csv-file.*  
 
The import and export commands have an optional parameter `--site-node` that can 
be used to specify the site for the import. If this parameter is not given the default of 
the current Neos-setup is used.

## Limitations 

The following issues and side effects are known at the moment:

1. Currently there is no way to notify the navigate component about a 
   needed reload. So after a node was moved behind the scene, the navigate 
   component will keep displaying the node on the current position until 
   the next reload.
2. The automatically created nodes are in the user workspace and still 
   have to be published. If you want to avoid this use the option ``autoPublishPath``. 

## Installation

Sitegeist.CriticalMass is available via packagist. Just add `"sitegeist/criticalmass" : "~1.0"` to the require-dev section of the composer.json or run `composer require --dev sitegeist/criticalmass`. We use semantic-versioning so every breaking change will increase the major-version number.

## License

see [LICENSE file](LICENSE)
