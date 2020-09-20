;(function($){

    var defaults = {
        parentSelector: '',
        scopeSelector: '',
        rules: []
    };

    var CreateDomRules = function( options ) {

        this.options    = $.extend({}, defaults, options);
        this.conditions = ['==', '===', '!=', '!==', '>', '>=', '<', '<=', 'any', 'not-any'];
        this.applyRules();

    };

    CreateDomRules.prototype.evalCallback = function( rule, condition ) {

        if ( condition ) {

            if ( rule.showTargets && typeof rule.showTargets == "function" ) {
                return rule.showTargets;
            } else if (this.options.showTargets && typeof this.options.showTargets == 'function') {
                return this.options.showTargets;
            } else {
                return this.showTargets;
            }

        } else {

            if ( rule.hideTargets && typeof rule.hideTargets == "function" ) {
                return rule.hideTargets;
            } else if (this.options.hideTargets && typeof this.options.hideTargets == 'function') {
                return this.options.hideTargets;
            } else {
                return this.hideTargets;
            }

        }

    };

    CreateDomRules.prototype.runRule = function( e ) {

        var 
        condition   = this.evalCondition( e.data.rule.condition, e.data.controller.val(), e.data.rule.value ),
        callback    = this.evalCallback( e.data.rule, condition );

        callback( e.data.rule, e.data.controller, condition, e.data.targets, e.data.scope );

    };

    CreateDomRules.prototype.applyRule = function( rule ) {

        var 
        scopeSelector = ( rule.scopeSelector ) ? rule.scopeSelector : this.options.scopeSelector,
        $scope        = $( this.options.parentSelector ).find( scopeSelector ),
        that          = this;

        $scope.each( function() {

            var 
            $controller = $(this).find( rule.controller ),
            $targets     = $(this).find( rule.targets ),
            data        = {
                rule        : rule,
                controller  : $controller,
                targets     : $targets,
                scope       : $scope
            };

            $controller.on('change', data, that.runRule.bind(that)).trigger('change', data);
            
        });

    };

    CreateDomRules.prototype.showTargets = function( rule, $controller, condition, $targets, $scope ) {

        $targets.show();

    };

    CreateDomRules.prototype.hideTargets = function( rule, $controller, condition, $targets, $scope ) {

        $targets.hide();

    };

    CreateDomRules.prototype.evalCondition = function( condition, val1, val2 ) {

        if ( this.conditions.indexOf( condition ) > -1 ) {

            switch (condition) {
                case "==": {
                    return val1 == val2;
                    break;
                }
                case "===": {
                    return val1 === val2;
                    break;
                }
                case "!=": {
                    return val1 != val2;
                    break;
                }
                case "!==": {
                    return val1 !== val2;
                    break;
                }
                case ">": {
                    return val1 > val2;
                    break;
                }
                case "<": {
                    return val1 < val2;
                    break;
                }
                case "any": {
                    return val2.indexOf(val1) >= 0;
                    break;
                }
                case "not-any": {
                    return val2.indexOf(val1) < 0;
                    break;
                }
            }

        } else {
            throw new Error("Unknown condition:" + condition);
        }

    };

    CreateDomRules.prototype.unbindEvents = function() {

        this.options.rules.forEach(function( rule ) {
            
            $( this.options.parentSelector ).find( rule.controller ).off('change');

        }.bind(this));

    };

    CreateDomRules.prototype.applyRules = function() {

        this.options.rules.forEach(function( rule ) {
            
            this.applyRule( rule );

        }.bind(this));

    };

    CreateDomRules.prototype.rulesUpdate = function() {

        this.unbindEvents();
        this.applyRules();

    };

    $.createDomRules = function( options ) {

        return new CreateDomRules( options );

    }

})(jQuery);


/* ============================== How to use

var domRules = $.createDomRules({

    parentSelector: 'body', // Require
    scopeSelector: '.promo-box-innner-shortcode', // Require for global scope, you can override it from single rule.
    showTargets:    function( rule, $controller, condition, $targets, $scope ) {}, // Optinal Global show function, you can override it from single rules.
    hideTargets:    function( rule, $controller, condition, $targets, $scope ) {}, // Optinal Global hide function, you can override it from single rules.

    rules: [
        {
            controller:     '.icon-type',
            value:          'icon',
            condition:      '==',           // it can be '==', '===', '!=', '!==', '>', '>=', '<', '<=', 'any', 'not-any';
            targets:        '.show-icon',   // if you need multiple then do like: '.show-icon, .other-dom'
            scopeSelector:  '.my-scope',    // Optional, it require if you have same controller & target twice.
            showTargets:    function( rule, $controller, condition, $targets, $scope ) {},  // Optional if you need
            hideTargets:    function( rule, $controller, condition, $targets, $scope ) {}   // Optional if you need
        },
        {
            controller:     '.icon-type',
            value:          'icon',
            condition:      '==',           // it can be '==', '===', '!=', '!==', '>', '>=', '<', '<=', 'any', 'not-any';
            targets:        '.show-icon',   // if you need multiple then do like: '.show-icon, .other-dom'
        },
        {
            controller:     '.icon-type',
            value:          ['icon', 'image'],
            condition:      'any',           // it can be '==', '===', '!=', '!==', '>', '>=', '<', '<=', 'any', 'not-any';
            targets:        '.show-common',   // if you need multiple then do like: '.show-icon, .other-dom'
        }
    ]
});

domRules.rulesUpdate(); // if you have new dom, then let's call it after updated the dom.
domRules.unbindEvents() // for unbind events

*/