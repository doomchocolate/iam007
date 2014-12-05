// name1 : $this -> element['name']
// name2 : $element['name']

function tzservices(id,name1,name2,tzvalue,prefix,verscompare,languages){
//    window.addEvent("domready",function(){
    if(!languages){
        languages   = {'langRemove': 'Remove','langEdit': 'Edit','langQuestion':'Are you sure want to delete this item?'};
    }
    var count = $$("#tzform-table .success").length,
        form = $("tz-form-" + name1),
        html = form.getProperty("html"),
        tbl = $("tz-table-" + name2);
    var required = false, rowCount = 1,
        _rowCount = tbl.getElement("tbody").getElements("tr").length;
    var columnName = new Array();

    // Add button click event
    $(prefix + "button_add").addEvent("click",function(e){
        e.preventDefault();

        if(tbl.getElement("tbody").getElements("tr")){
            tbl.getElement("tbody").getElements("tr").each(function(obj){
                var trId = obj.getProperty("id");
                if(rowCount <= parseInt(trId.replace(prefix + "row",""))){
                    rowCount = parseInt(trId.replace(prefix + "row","")) + 1;
                }
            });
        }

        // Task hidden
        var taskHidden = $$("input[name=\""+ name2 +"_task\"]");
        if(taskHidden.getProperty("value") != -1){
            rowCount = taskHidden.getProperty("value");
        }

        var value  = "", tblArr = new Array(), firstValue;
        if(form.getElements("input,select,textarea")){
            try {
                firstValue =  form.getElement("input,select,textarea").value;
                form.getElements("input,select,textarea").each(function(obj,index){
                    if(obj.getProperty("required")){
                        if(!obj.value){obj.focus(); throw "break";}
                        tblArr[index] = obj.value;
                        required = true;
                    }

//                    if(obj.getProperty("multiple")){
//                        if(index!=0){
//                            value +=", ";
//                        }
//                        var listVal = obj.getSelected().map(function(e) { return "\""+e.value+"\""; });
//                        value += "\""+obj.getProperty("name").replace(prefix,"")+"\":["+listVal.toString()+"]";
//                        obj.getElements("option").map(function(e){e.erase("selected");});
//                        if(verscompare == 1){
//                            $$("#tz-form-"+ name1 +" .search-choice").dispose();
//                            $$("#tz-form-"+ name1 +" .chzn-results li").setProperty("class","active-result");
//                        }
//                    }else{
                        if(obj.getProperty("name")){
                            if(obj.get("tag") == "textarea" && $(obj.getProperty("id")+"_ifr")){
                                if(index!=0){
                                    value +="\, ";
                                }
                                if(obj.style.display && obj.style.display == "none"){
                                    value += "\""+obj.getProperty("name").replace(prefix,"")
                                        .replace(/\"/gi,"\\\"").replace(/\{/gi,"\\\{").replace(/\}/gi,"\\\}")+"\":\""+
                                        $(obj.getProperty("id")+"_ifr").contentDocument.body.innerHTML.replace(/\<br data-mce-bogus=\"1\"\>/,"")
                                            .replace(/\"/gi,"\\\"").replace(/\}/gi,"\\\}").replace(/\{/gi,"\\\{")+"\"";
                                    $(obj.getProperty("id")+"_ifr").contentDocument.body.innerHTML = "";
                                }else{
                                    value += "\""+obj.getProperty("name").replace(prefix,"")
                                        .replace(/\"/gi,"\\\"")+"\":\""+obj.getProperty("value")
                                        .replace(/\"/gi,"\\\"")+"\"";
                                    obj.setProperty("value","");
                                }
                            }else{
                                if(obj.get('tag') == 'select'){
                                    if(index!=0){
                                        value +=", ";
                                    }
                                    var listVal = "";
                                    if(obj.getProperty("multiple")){
                                        listVal = obj.getSelected().map(function(e) { return "\\\""+e.value+"\\\""; });
                                        value += "\""+obj.getProperty("name").replace(prefix,"")
                                            +"\":\"["+listVal.toString()+"]\"";
                                    }else{
                                        listVal = obj.getSelected().map(function(e) { return e.value; });
                                        value += "\""+obj.getProperty("name").replace(prefix,"")
                                            .replace(/\[/gi,"\\\[").replace(/\]/gi,"\\\]")+"\":\""+listVal.toString()+"\"";
                                    }
                                    obj.getElements("option").map(function(e){e.erase("selected");});
                                    if(verscompare == 1){
                                        $$("#tz-form-"+ name1 +" .search-choice").dispose();
                                        $$("#tz-form-"+ name1 +" .chzn-results li").setProperty("class","active-result");
                                    }
                                }else{
                                    switch (obj.getProperty('type')){
                                        default :
                                        case 'text':
                                        case 'textarea':
                                            if(index!=0){
                                                value +="\, ";
                                            }
                                            value += "\""+obj.getProperty("name").replace(prefix,"")
                                                .replace(/\"/gi,"\\\"")+"\":\""+obj.getProperty("value")
                                                .replace(/\"/gi,"\\\"")+"\"";
                                            obj.setProperty("value","");
                                            break;
                                        case 'radio':
                                            if(verscompare == 1){ // If Joomla version is 3.x
                                                if($(obj.getProperty('name')).hasClass('btn-group')){
                                                    if($$('label[for="'+obj.getProperty('id')+'"]').hasClass('active')){
                                                        var bool    = $$('label[for="'+obj.getProperty('id')+'"]').hasClass('active');
                                                        if(bool[0]){
                                                            if(index!=0){
                                                                value +="\, ";
                                                            }
                                                            value += "\""+obj.getProperty("name").replace(prefix,"")
                                                                .replace(/\"/gi,"\\\"")+"\":\""+obj.getProperty("value")
                                                                .replace(/\"/gi,"\\\"")+"\"";
                                                        }
                                                    }
                                                }else{
                                                    if(obj.checked){
                                                        if(index!=0){
                                                            value +="\, ";
                                                        }
                                                        value += "\""+obj.getProperty("name").replace(prefix,"")
                                                            .replace(/\"/gi,"\\\"")+"\":\""+obj.getProperty("value")
                                                            .replace(/\"/gi,"\\\"")+"\"";
                                                    }
                                                }
                                            }else{
                                                if(obj.checked){
                                                    if(index!=0){
                                                        value +="\, ";
                                                    }
                                                    value += "\""+obj.getProperty("name").replace(prefix,"")
                                                        .replace(/\"/gi,"\\\"")+"\":\""+obj.getProperty("value")
                                                        .replace(/\"/gi,"\\\"")+"\"";
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                        }
//                    }
                });
                if(value.length){
                    value ="{"+value+"}";
                }
                if(!required){ if(firstValue.length){tblArr[0] = firstValue;}}
                TzCreateBody("tz-table-"+ name2,tblArr,value,rowCount);
            }catch(e){ if(e != "break") throw e; }
        }
    });

    // Cancel click button
    $(prefix + "button_cancel").addEvent("click",function(){
        if(form.getElements("input,select,textarea")){
            form.getElements("input,select,textarea").map(function(e){
                if(e.get("tag") == "textarea" && $(e.getProperty("id")+"_ifr")){
                    $(e.getProperty("id")+"_ifr").contentDocument.body.innerHTML = "";
                }
                if(e.getProperty("name")){
                    e.setProperty("value","");
                    $$("input[name=\""+ name2 +"_task\"]").setProperty("value","-1");

                    // Get row max
                    if(tbl.getElement("tbody").getElements("tr")){
                        tbl.getElement("tbody").getElements("tr").each(function(obj){
                            var trId = obj.getProperty("id");
                            if(rowCount < parseInt(trId.replace(prefix + "row",""))){
                                rowCount = parseInt(trId.replace(prefix + "row",""));
                            }
                        });
                    }
                }
            });
        }
    });
//    });

    // Create table's body function
    var TzCreateBody  = function(tableId,tblArr,hiddenValue,count){
        var table = $(tableId);
        if(tblArr.length){
            var trclass = null;
            var _tr = table.getElement("tbody tr:last-child");
            if(_tr){
                _rowCount = parseInt(_tr.getProperty("id").replace(prefix + "row",""));
            }
            if(verscompare == 1){
                switch (_rowCount%2){
                    default:
                    case 0: trclass="info "; break;
                    case 1: trclass="error "; break;
                    case 2: trclass="success "; break;
                }
            }
            trclass +="row"+(_rowCount%2);
            var taskHidden = $$("input[name=\""+ name2 +"_task\"]");
            if(taskHidden.getProperty("value") != -1 || !taskHidden.getProperty("value")){
                var tr=$(prefix + "row"+taskHidden.getProperty("value"));
                tr.setProperty("html","");
                taskHidden.setProperty("value","-1");
            }else{
                var tr = new Element("tr",{
                    class: "rl " + trclass,
                    id: prefix + "row"+count
                }).inject(table.getElement("tbody"));
            }

            var td1 = new Element("td",{class: "index",html: count}).inject(tr);
            tblArr.each(function(name,key){
                var td2 = new Element("td",{"html": name}).inject(tr);
            });
            var td  = new Element("td",{class: "center"}).inject(tr);
            var edit = new Element("button",{
                    type: "button",
                    class: "btn btn-small",
                    value: count,
                    html: "<i class=\"icon-edit\"></i> "+languages.langEdit,
                    events:{
                        "click": function(){
                            var eObj = $(prefix + "row"+count);
                            if(eObj.getElement("input")){
                                $$("input[name=\""+ name2 +"_task\"]").setProperty("value",this.value);
                                eObj    = JSON.parse(Base64.decode(eObj.getElement("input").value).replace(/\\\{/gi,"{").replace(/\\\}/gi,"}"));
                                form.getElements("input,select,textarea").each(function(obj,index){
                                    if(obj.getProperty("name")){
//                                        if(obj.getProperty("multiple")){
//                                            if(typeof  eObj[obj.getProperty("name").replace(prefix,"")] != "string"){
//                                                var eValue  = eObj[obj.getProperty("name").replace(prefix,"")];
//                                                obj.getElements("option").map(function(e){
//                                                    if(eValue && eValue.indexOf(e.value)){
//                                                        e.setProperty("selected","selected");
//                                                    }
//                                                    else{
//                                                        e.erase("selected");
//                                                    }
//                                                });
//                                            }
//                                        }
//                                        else{
                                            if(obj.get("tag") == "textarea" && $(obj.getProperty("id")+"_ifr")){
                                                if(obj.style.display && obj.style.display == "none"){
                                                    $(obj.getProperty("id")+"_ifr").contentDocument.body.innerHTML=eObj[obj.getProperty("name").replace(prefix,"")];
                                                }else{
                                                    obj.setProperty("value",eObj[obj.getProperty("name").replace(prefix,"")]);
                                                }
                                            }else{
                                                if(obj.get('tag') == 'select'){

                                                    var eValue  = (eObj[obj.getProperty("name").replace(prefix,"")]);

                                                    try {
                                                        eValue = JSON.parse(eValue);
                                                    } catch (e) {
                                                        // is not a valid JSON string
                                                    }

                                                    obj.getElements("option").map(function(e,index){
                                                        if(typeof eValue == "object"){
                                                            eValue  = eval(eValue);
                                                            e.erase("selected");
                                                            for(var i = 0;i<eValue.length; i++){
                                                                if(e.value == eValue[i]){
                                                                    e.setProperty("selected","selected");
//                                                                    if(verscompare == 1){
//                                                                        var opobj   = new Element('<li#tzform_list_chzn_c_'+ index,{
//                                                                            class: "search-choice",
//                                                                            html: '<span>'+ e.getProperty('html')+'</span><a rel="'+ index +'" class="search-choice-close" href="javascript:void(0)"></a>'
//                                                                        });
//                                                                        $$("#"+ obj.getProperty('name').replace(/\[/gi,'').replace(/\]/gi,'') +"_chzn .chzn-choices .search-field")
//                                                                            .grab(opobj,'before');
//                                                                    }
                                                                }
                                                            }
                                                        }else{
                                                            if(e.value == eValue){
                                                                e.setProperty('selected','selected');
//                                                                if(verscompare == 1){
//                                                                    $$("#"+ obj.getProperty('name') +"_chzn .chzn-single span").setProperty('html',e.getProperty('html'));
//                                                                }
                                                            }
                                                            else{
                                                                e.erase("selected");
                                                            }
                                                        }
                                                    });
                                                    if(verscompare == 1){
                                                    $$('select[name="'+obj.getProperty('name')+'"]').removeClass('chzn-done');
                                                    $$('#'+obj.getProperty('id')+'_chzn').destroy();
                                                        jQuery('select[name="'+obj.getProperty('name')+'"]').chosen({
                                                            disable_search_threshold : 10,
                                                            allow_single_deselect : true
                                                        });
                                                    }
                                                }else{
                                                    switch (obj.getProperty('type')){
                                                        default :
                                                        case 'text':
                                                        case 'textarea':
                                                            obj.setProperty("value",eObj[obj.getProperty("name").replace(prefix,"")]);
                                                            break;
                                                        case 'radio':
                                                            if(verscompare == 1){ // If Joomla version is 3.x
                                                                if($(obj.getProperty('name')).hasClass('btn-group')){
                                                                    $$('label[for="'+obj.getProperty('id')+'"]').setProperty('class','btn');
                                                                    if(obj.value == eObj[obj.getProperty("name").replace(prefix,"")]){
                                                                        switch (eObj[obj.getProperty("name").replace(prefix,"")]){
                                                                            default :
                                                                            case '1':
                                                                                $$('label[for="'+obj.getProperty('id')+'"]').addClass('active btn-success');
                                                                                break;
                                                                            case '0':
                                                                                $$('label[for="'+obj.getProperty('id')+'"]').addClass('active btn-danger');
                                                                                break;
                                                                            case '':
                                                                                $$('label[for="'+obj.getProperty('id')+'"]').addClass('active btn-primary');
                                                                                break;
                                                                        }
                                                                    }
                                                                }else{
                                                                    if(obj.value == eObj[obj.getProperty("name").replace(prefix,"")]){
                                                                        obj.setProperty('checked','checked');
                                                                    }
                                                                }
                                                            }else{
                                                                if(obj.value == eObj[obj.getProperty("name").replace(prefix,"")]){
                                                                    obj.setProperty('checked','checked');
                                                                }
                                                            }
                                                            break;
                                                    }
                                                }
                                            }
//                                        }
                                    }
                                });
                            };
                        }
                    }
                }).inject(td),
                del = new Element("button",{
                    type: "button",
                    class: "btn btn-small",
                    value: count,
                    html: "<i class=\"icon-trash\"></i> "+languages.langRemove,
                    events:{
                        "click": function(){
                            var message = confirm(languages.langQuestion);
                            if(message){
                                $(prefix + "row"+count).destroy();
                            }
                        }
                    }
                }).inject(td),
                inputValue = new Element("input",{
                    type: "hidden",
                    name: prefix + "hidden_"+ name2 +"[]",
                    value: Base64.encode(hiddenValue.trim())
                }).inject(td);
        }
    };

    // Init data
    var initData = function(jsonData){
        var m = Base64.decode(jsonData).match(/(\{".*?"\})/g);
        var rowCount = 1;
        if(m && m.length){
            m.each(function(value,key){
                var objV = JSON.parse(value.replace(/\\\{/gi,"{").replace(/\\\}/gi,"}"));
                var tblArr = new Array(), firstValue2 = "", required2 = false;
                if(form.getElements("input,select,textarea")){
                    try {
                        form.getElements("input,select,textarea").each(function(obj,index){
                            if(obj.getProperty('name') && obj.getProperty('name').length){
//                                if(obj.getProperty('name').indexOf(prefix)){
                                    var _name = obj.getProperty("name").replace(prefix,"");
                                    if(index == 0){
                                        firstValue2 =  objV[_name];
                                    }
                                    if(obj.getProperty("required")){
                                        tblArr[index] = objV[_name];
                                        required2 = true;
                                    }
//                                }
                            }
                        });
                        if(!required2){ if(firstValue2.length){tblArr[0] = firstValue2;}}
                        TzCreateBody("tz-table-"+ name2,tblArr,value,rowCount);
                    }catch(e){if(e != "break") throw e;};
                }
                _rowCount++;
                rowCount++;
            });
        }
    };
    initData(tzvalue);

    // Get data function to submit form
    var tzgetData = function(){
        var obj = $$("input[name=\""+ prefix +"hidden_"+ name2 +"[]\"]");
        if(obj.length){
            var str = "",i=0;
            Object.each(obj.getProperty("value"),function(value,key){
                if(value.trim().length){
                    str = str.trim();
                    str += Base64.decode(value.trim()).toString();
                    if(i< (obj.length - 1)){
                        str += "\,";
                    }
                    i++;
                }
            });

            if(str.length){
                return Base64.encode(str);
            }
            return "";
        }
        return "";
    };

    document.adminForm.onsubmit= function(){
        $(id).setProperty("value",tzgetData());
    }
}
