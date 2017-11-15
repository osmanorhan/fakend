{
    "attrs": [
        {
            "fieldName": "title",
            "attributeType": "title",
            "parameters": {"length":4}
        },
        {
            "fieldName": "body",
            "attributeType": "description",
            "parameters": {"length":200,"html":true}
        },
        {
            "fieldName": "tag",
            "attributeType": "random",
            "parameters": {"values": ["human","robot","android"]}
        },
        {
            "fieldName": "date",
            "attributeType": "date",
            "parameters": {"from":"-4 year","to":"+1 year"}
        },
        {
            "fieldName": "count",
            "attributeType": "numberBetween",
            "parameters": {"min":10,"max":1000}
        },
        {
            "fieldName": "url",
            "attributeType": "url",
            "parameters": {}
        }
    ],
    "belongsTo": [
        {
            "fieldName": "author",
            "attributeType": "author",
            "parameters": {"required":true}
        }
    ],
    "hasMany": [
        {
            "fieldName": "comments",
            "attributeType": "comment",
            "parameters": {"required":false}
        }
    ]
}