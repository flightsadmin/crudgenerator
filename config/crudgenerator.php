<?php

return [
    "middlewares" => [
        "crudViews" => "auth",
        "createdViews" => "auth"
    ],

    "presets" => [
        "string" => [
            "datatype" => "string",
            "validation" => "string|min:2|max:255",
            "input" => "text",
            "factory" => "name"
        ],  
		"flight_no" => [
            "datatype" => "string",
            "validation" => "string|min:2|max:20",
            "input" => "text",
            "factory" => "bothify('XA###')"
        ],
		"registration" => [
            "datatype" => "string",
            "validation" => "string|min:2|max:20",
            "input" => "select",
            "factory" => "bothify('A4O-###')"
        ],
		"flight_type" => [
            "datatype" => "string",
            "validation" => "string|min:2|max:20",
            "input" => "select",
            "factory" => "randomElement(['Arrival', 'Departure', 'Turnaround'])"
        ],
        "integer" => [
            "datatype" => "integer",
            "validation" => "integer",
            "input" => "number",
            "factory" => "numberBetween(1, 50)"
        ],
        "text" => [
            "datatype" => "text",
            "validation" => "string|min:5",
            "input" => "textarea",
            "factory" => "realText(100)"
        ],
        "boolean" => [
            "datatype" => "boolean",
            "validation" => "boolean",
            "input" => "checkbox",
            "factory" => "boolean"
        ], 
		"select" => [
            "datatype" => "string",
            "validation" => "string",
            "input" => "select",
            "factory" => "bothify('A4O-###')"
        ],
        "date" => [
            "datatype" => "date",
            "validation" => "date",
            "input" => "date",
            "factory" => "dateTimeThisYear()"
        ], 
        "time" => [
            "datatype" => "time",
            "validation" => "time",
            "input" => "time",
            "factory" => "time()"
        ], 
		"timestamp" => [
            "datatype" => "timestamp",
            "validation" => "date",
            "input" => "date",
            "factory" => "dateTimeThisYear()"
        ],
        "email" => [
            "datatype" => "text",
            "validation" => "email",
            "input" => "email",
            "factory" => "unique()->safeEmail"
        ],
        "file" => [
            "datatype" => "string",
            "validation" => "file",
            "input" => "file",
            "factory" => null
        ]
    ]
];
