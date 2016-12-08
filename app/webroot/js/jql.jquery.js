(function() {
    var fn = {
        query:function(selector, source) {
            var matches = jql.util.isType(jql.type.string, selector) ? source.find(selector) : source;
            var records = fn.toArray(matches);
            var query = jql.from(records);
            query.$ = source;
            return query;
        },
        findTarget:function(selector, source) {
            if (selector instanceof jQuery) return selector;
            if (jql.util.isType(jql.type.string, selector)) source = source.find(selector);
            return selector;
        },
        select:function(selector, records) {
            var selection = $(records);
            return jql.util.isType(jql.type.string, selector) ? selection.find(selector) : selection;
        },
        toArray:function(obj) {
            var records = [];
            obj.each(function(i, v) { records.push($(v)); });
            return records;
        }
    };
    
    jql.extend([
        { 
            name:"$", 
            type:jql.command.select,
            method:function() { return fn.select(selector, this.records); }
        },
        { 
            name:"get", 
            type:jql.command.select,
            method:function(selector) { return fn.select(selector, this.records); }
        },
        { 
            name:"include", 
            type:jql.command.action,
            method:function(selector, source) {
                source = source || this.query.$; var matches = source.find(selector); var records = fn.toArray(matches); this.records = this.records.concat(records);
            }
        }
    ]);
    $.fn.query = function(selector) {return fn.query(selector, this);}
    jql.$ = function(selector) { return fn.query(selector, $(document.body)); };
})();