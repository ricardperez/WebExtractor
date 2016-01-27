# WebExtractor
A PHP library that will allow you to get data from websites like if they had a REST API.

It is possible to extract data from normal HTML websites or to get it from a JSON result.

The most important thing to do to use this library is to define a descriptions.json file that will be given to the WebExtractor::extractAll() method.

This is a sample descriptions.json file contents:

{
	"files":
	[
		{
			"name": "A not so important name for website1",
			"url": "http://www.website1.com?param1={{value1}}&param2={{value2}}",
			"type": "json",
			"file": "path/to/website1_json_desc.json"
		},
		{
			"name": "A not so important name for website2",
			"url": "http://www.website2.com?param1={{value1}}&param2={{value2}}",
			"type": "dom",
			"file": "path/to/website2_dom_desc.json"
		}
	]
}

So this json has a "files" array that contains a description for each website where it will extract elements from.
This is the parameters description:
  - name: a name given to this website. This will be given to each resulting element for the key ['site'].
  - url: the URL to call using curl and the results of which will be parsed
  - type: has to be either "json" or "dom" depending on what kind of result the URL will return.
  - file: the path to the file that explains how the URL results have to be parsed.


Here I let you 2 examples of website specific description json files (one for the dom type, and one for the json type).


website1_json_desc.json:
  {
    "id": "an identifier",
    "elementsList":
    [
      "data",
      "items_container"
    ],
    "element":
    [
      {
        "attribute": "image",
        "schema":
        [
          "mainImage",
          "smallURL"
        ]
      },
      {
        "attribute": "price",
        "schema":
        [
          "salePrice"
        ],
        "format": "{{%|number|decimals(2)}}€"
      }
    ]
  }

  - id: this attribute is not being used at the moment
  - elementsList: an array that will let the parser know how to access the elements array in the json got from the URL. Each of its elements has to be a key of the last object. The last one has to be the one that will give it the array of elements.
  - element: This is an array that describes what attributes each element has and how to get them.
      - attribute: the attribute name used as a key for the resulting value in the element array.
      - schema: an array of strings similar to the elementsList that will be used to access the attribute value.
      - format: (optional) a string to format the value. See the AttributeFormatParser.php documentation for more details.


website2_dom_desc.json
  {
    "id": "another identifier",
    "elementsList":
    [
      {
        "type": "div",
        "filters":
        [
          {
            "attribute": "id",
            "value": "global_div_id"
          }
        ]
      },
      {
        "type": "div",
        "filters":
        [
          {
            "attribute": "class",
            "value": "container_div_class"
          }
        ]
      }
    ],
    "element":
    [
      {
        "attribute": "image",
        "schema":
        [
          {
            "type": "div",
            "filters":
            [
              {
                "attribute": "class",
                "value": "vefbox"
              }
            ]
          },
          {
            "type": "a"
          }
        ],
        "value":
        {
          "type": "attribute",
          "attribute": "href",
          "format": "http://another.url.com/static/images/{{%|lastCharacters(9)}}.jpg"
        }
      },
      {
        "attribute": "price",
        "schema":
        [
          {
            "type": "div",
            "filters":
            [
              {
                "attribute": "class",
                "value": "x7"
              }
            ]
          },
          {
            "type": "div",
            "filters":
            [
              {
                "attribute": "class",
                "value": "x11"
              }
            ]
          },
          {
            "type": "div",
            "filters":
            [
              {
                "attribute": "class",
                "value": "pr"
              }
            ]
          }
        ],
        "value":
        {
          "type": "value"
        }
      }
    ]
  }

  - id: this attribute is not being used at the moment
  - elementsList: an array that will let the parser know how to access the elements array in the json got from the URL. Each of its elements has the following parameters. The last one has to drive the parser to a container that contains all the elements dom nodes.
      - type: the html tag name to look for (for example div, table...)
      - filters: (optional) an array to filter all the possible dom nodes that have the specified type to a subset. This is an array of filter objects, each of them having the following description:
          - attribute: the attribute name inside the tag. For example we could have this div: <div class="a_div_class"></div>, for which we could specify a filter for specifying the class "a_div_class". In this case, the attribute value would be class.
          - value: In the previous example, this value would be "a_div_class".
  - element: This is an array that describes what attributes each element has and how to get them.
      - attribute: the attribute name used as a key for the resulting value in the element array.
      - schema: an array of objects similar to the elementsList that will be used to access the dom node.
      - value: the way to get the attribute value from the last dom node obtained from the schema. It has the following description:
          - type: either "value" or "attribute". If it is "value", the resulting dom node value will be used, otherwise, some more parameters will exist to get it.
          - attribute: (only if type is attribute) Similar to the filter attribute value, it will be used to get the element attribute value from this dom node attribute.
          - format: (optional) a string to format the resulting value. See the AttributeFormatParser.php documentation for more details.
