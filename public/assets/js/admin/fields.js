adminFormFields = {
    /* Begin Users Form Fields */
    "users" :  [
        {
          "label": "First Name",
          "name": "first_name"
        },
        {
          "label": "Last Name",
          "name": "last_name"
        },
        {
          "label": "Email",
          "name": "email"
        },        
        {
            "label": "Identity Provider",
            "name": "idp",
            "type": "select",
            "choices":"/api/idps",
            "enabled":false   
        },
        {
          "label": "ID",
          "name": "unique_id"
        },
        {
          "type": "hidden",
          "label": "ID",
          "name": "id"
        },
      ],
    /* End Users Form Fields */
    "teams" : [
      {
        "label": "Name",
        "name": "name"
      },
      {
        "label": "Scenario",
        "name": "scenario_id",
        "type": "select",
        "choices":"/api/scenarios"
      },
      {
        "type": "hidden",
        "label": "ID",
        "name": "id"
      }  
    ],
    "scenarios" : [
      {
        "label": "Name",
        "name": "name"
      },
      {
        "label": "Report to learner",
        "name": "summary_description",
        "type": "textarea"
      },
      {
        "label": "Case Synopsis",
        "name": "synopsis_description",
        "type": "textarea"
      },       
      {"name": "id","type": "hidden"} 
  
    ],
    "products" : [
      {"label": "Name/Strength/dosage","name":"name"},
      {"label": "Shape","name":"shape"},
      {"label": "Color","name":"color"},
      {"label": "Imprint","name":"imprint"},
      {"label": "Barcode","name":"barcode"},		
      {"label": "Image","name":"image","type":"base64","template":"{{#attributes.image}}<img style=\"max-width:120px\" src=\"/api/library/products/{{attributes.id}}/img\"/>{{/attributes.image}}"},
      {"name": "id","type": "hidden"} 
  
    ],
    "prescribers" : [
      {"label": "First Name","name":"first_name"},
      {"label": "Last Name","name":"last_name"},
      {"label": "Address","name":"address"},
      {"label": "Phone","name":"phone"},
      {"label": "DEA","name":"dea"},
      {"label": "NPI","name":"npi"},
      {"name": "id","type": "hidden"} 
  
    ],
    "solutions" : [
      {"label": "Abbreviation","name":"abbreviation"},
      {"label": "Solution Name/Strength","name":"name"},
      {"label": "Volume","name":"volume"},
      {"name": "id","type": "hidden"} 
  
    ],
    "labs" : [
      {"label": "Test","name":"test"},
      {"label": "Full name","name":"label"},
      {"label": "Unit","name":"unit"},
      {"label": "Abnormal Calculation","name":"ab_calc","options":["None","Range","Other"],"type":"select"},
      {"label": "Min","name":"min","show":{"matches":{"name":"ab_calc","value":"Range"}}},
      {"label": "Max","name":"max","show":{"matches":{"name":"ab_calc","value":"Range"}}},
      {"label": "Reference Range","name":"range","show":{"not_matches":{"name":"ab_calc","value":"None"}}},
      {"label": "Category","name":"category","options":
          ["other","abgs","bmp","cmpanel", "cbc","cmprofile","ck","electrolytes","lp","lfp","urinalysis","btc","csf","coagulation"]
      ,"type":"select"},
      {"name": "id","type": "hidden"}
    ],
    "members" : [
        {
            "type": "select",
            "label": "User",
            "name": "user_id",
            "label_key": "email",
            "value_key": "id",
            "choices":"/api/users"
        },
        {
            "type": "select",
            "label": "Role",
            "name": "role_id",
            "label_key": "title",
            "value_key": "id",
            "choices":"roles",
            "choices":"/api/roles"
        },
        {
            "type": "checkbox",
            "label": "Team Admin",
            "name": "admin"
        },
        {
            "name": "team_id",
            "type":"hidden"
        }
      ],
      "messages":[
        {
            "label": "Message",
            "name": "message",
            "type":"textarea"
        },
        {
            "label": "User",
            "name": "user_id",
            "template": "{{attributes.user.first_name}} {{attributes.user.last_name}} - {{attributes.user.email}}",
            "show":false,
        },
        {
            "label": "Timestamp",
            "name": "created_at",
            "show":false,
        },
        {
            "name": "team_id",
            "type":"hidden"
        }   
      ],
      "notes":[
        {
            "label": "Note",
            "name": "note",
            "type":"textarea"
        },
        {
            "label": "User",
            "name": "user_id",
            "template": "{{attributes.user.first_name}} {{attributes.user.last_name}} - {{attributes.user.email}}",
            "show":false,
        },
        {
            "label": "Timestamp",
            "name": "created_at",
            "show":false,
        },
        {
            "name": "team_id",
            "type":"hidden"
        }      
      ]
  }