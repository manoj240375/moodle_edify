YUI.add('moodle-enrol-enrolmentmanager', function(Y) {

    var UEP = {
        NAME : 'Enrolment Manager',
        /** Properties **/
        BASE : 'base',
        SEARCH : 'search',
        PARAMS : 'params',
        URL : 'url',
        AJAXURL : 'ajaxurl',
        MULTIPLE : 'multiple',
        PAGE : 'page',
        COURSEID : 'courseid',
        USERS : 'users',
        USERCOUNT : 'userCount',
        REQUIREREFRESH : 'requiresRefresh',
        LASTSEARCH : 'lastPreSearchValue',
        INSTANCES : 'instances',
        OPTIONSTARTDATE : 'optionsStartDate',
        DEFAULTROLE : 'defaultRole',
        DEFAULTSTARTDATE : 'defaultStartDate',
        DEFAULTDURATION : 'defaultDuration',
        ASSIGNABLEROLES : 'assignableRoles'
    };
    /** CSS classes for nodes in structure **/
    var CSS = {
        PANEL : 'user-enroller-panel',
        WRAP : 'uep-wrap',
        HEADER : 'uep-header',
        CONTENT : 'uep-content',
        AJAXCONTENT : 'uep-ajax-content',
        SEARCHRESULTS : 'uep-search-results',
        TOTALUSERS : 'totalusers',
        USERS : 'users',
        USER : 'user',
        MORERESULTS : 'uep-more-results',
        LIGHTBOX : 'uep-loading-lightbox',
        LOADINGICON : 'loading-icon',
        FOOTER : 'uep-footer',
        ENROL : 'enrol',
        ENROLLED : 'enrolled',
        COUNT : 'count',
        PICTURE : 'picture',
        DETAILS : 'details',
        FULLNAME : 'fullname',
        EMAIL : 'email',
        OPTIONS : 'options',
        ODD  : 'odd',
        EVEN : 'even',
        HIDDEN : 'hidden',
        SEARCHOPTIONS : 'uep-searchoptions',
        COLLAPSIBLEHEADING : 'collapsibleheading',
        COLLAPSIBLEAREA : 'collapsiblearea',
        SEARCHOPTION : 'uep-enrolment-option',
        SEARCHCONTROLS : 'uep-controls',
        ROLE : 'role',
        STARTDATE : 'startdate',
        DURATION : 'duration',
        ACTIVE : 'active',
        SEARCH : 'uep-search',
        CLOSE : 'close'
    };

    var USERENROLLER = function(config) {
        USERENROLLER.superclass.constructor.apply(this, arguments);
    };
    Y.extend(USERENROLLER, Y.Base, {
        _searchTimeout : null,
        _loadingNode : null,
        _escCloseEvent : null,
        initializer : function(config) {
            this.set(UEP.BASE, Y.Node.create('<div class="'+CSS.PANEL+' '+CSS.HIDDEN+'"></div>')
                .append(Y.Node.create('<div class="'+CSS.WRAP+'"></div>')
                    .append(Y.Node.create('<div class="'+CSS.HEADER+' header"></div>')
                        .append(Y.Node.create('<div class="'+CSS.CLOSE+'"></div>'))
                        .append(Y.Node.create('<h2>'+M.str.enrol.enrolusers+'</h2>')))
                    .append(Y.Node.create('<div class="'+CSS.CONTENT+'"></div>')
                        .append(Y.Node.create('<div class="'+CSS.SEARCHCONTROLS+'"></div>')
                            .append(Y.Node.create('<div class="'+CSS.SEARCHOPTION+' '+CSS.ROLE+'">'+M.str.role.assignroles+'</div>')
                                    .append(Y.Node.create('<select><option value="">'+M.str.enrol.none+'</option></select>'))
                            )
                            .append(Y.Node.create('<div class="'+CSS.SEARCHOPTIONS+'"></div>')
                                .append(Y.Node.create('<div class="'+CSS.COLLAPSIBLEHEADING+'"><img alt="" />'+M.str.enrol.enrolmentoptions+'</div>'))
                                .append(Y.Node.create('<div class="'+CSS.COLLAPSIBLEAREA+' '+CSS.HIDDEN+'"></div>')
                                    .append(Y.Node.create('<div class="'+CSS.SEARCHOPTION+' '+CSS.STARTDATE+'">'+M.str.moodle.startingfrom+'</div>')
                                        .append(Y.Node.create('<select></select>')))
                                    .append(Y.Node.create('<div class="'+CSS.SEARCHOPTION+' '+CSS.DURATION+'">'+M.str.enrol.enrolperiod+'</div>')
                                        .append(Y.Node.create('<select><option value="0" selected="selected">'+M.str.enrol.unlimitedduration+'</option></select>')))
                                )
                            )
                        )
                        .append(Y.Node.create('<div class="'+CSS.AJAXCONTENT+'"></div>'))
                        .append(Y.Node.create('<div class="'+CSS.LIGHTBOX+' '+CSS.HIDDEN+'"></div>')
                            .append(Y.Node.create('<img alt="loading" class="'+CSS.LOADINGICON+'" />')
                                .setAttribute('src', M.util.image_url('i/loading', 'moodle')))
                            .setStyle('opacity', 0.5)))
                    .append(Y.Node.create('<div class="'+CSS.FOOTER+'"></div>')
                        .append(Y.Node.create('<div class="'+CSS.SEARCH+'"><label>'+M.str.enrol.usersearch+'</label></div>')
                            .append(Y.Node.create('<input type="text" id="enrolusersearch" value="" />'))
                        )
                    )
                )
            );

            this.set(UEP.SEARCH, this.get(UEP.BASE).one('#enrolusersearch'));
            Y.all('.enrolusersbutton input').each(function(node){
                if (node.getAttribute('type', 'submit')) {
                    node.on('click', this.show, this);
                }
            }, this);
            this.get(UEP.BASE).one('.'+CSS.HEADER+' .'+CSS.CLOSE).on('click', this.hide, this);
            this._loadingNode = this.get(UEP.BASE).one('.'+CSS.CONTENT+' .'+CSS.LIGHTBOX);
            var params = this.get(UEP.PARAMS);
            params['id'] = this.get(UEP.COURSEID);
            this.set(UEP.PARAMS, params);

            Y.on('key', this.preSearch, this.get(UEP.SEARCH), 'down:13', this);

            Y.one(document.body).append(this.get(UEP.BASE));

            var base = this.get(UEP.BASE);
            base.plug(Y.Plugin.Drag);
            base.dd.addHandle('.'+CSS.HEADER+' h2');
            base.one('.'+CSS.HEADER+' h2').setStyle('cursor', 'move');


            this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEHEADING).one('img').setAttribute('src', M.util.image_url('t/collapsed', 'moodle'));
            this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEHEADING).on('click', function(){
                this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEHEADING).toggleClass(CSS.ACTIVE);
                this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEAREA).toggleClass(CSS.HIDDEN);
                if (this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEAREA).hasClass(CSS.HIDDEN)) {
                    this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEHEADING).one('img').setAttribute('src', M.util.image_url('t/collapsed', 'moodle'));
                } else {
                    this.get(UEP.BASE).one('.'+CSS.SEARCHOPTIONS+' .'+CSS.COLLAPSIBLEHEADING).one('img').setAttribute('src', M.util.image_url('t/expanded', 'moodle'));
                }
            }, this);

            this.populateAssignableRoles();
            this.populateStartDates();
            this.populateDuration();
        },
        populateAssignableRoles : function() {
            this.on('assignablerolesloaded', function(){
                var roles = this.get(UEP.ASSIGNABLEROLES);
                var s = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.ROLE+' select');
                var v = this.get(UEP.DEFAULTROLE);
                var index = 0, count = 0;
                for (var i in roles) {
                    count++;
                    var option = Y.Node.create('<option value="'+i+'">'+roles[i]+'</option>');
                    if (i == v) {
                        index = count;
                    }
                    s.append(option);
                }
                s.set('selectedIndex', index);
            }, this);
            this.getAssignableRoles();
        },
        populateStartDates : function() {
            var select = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.STARTDATE+' select');
            var defaultvalue = this.get(UEP.DEFAULTSTARTDATE);
            var options = this.get(UEP.OPTIONSTARTDATE);
            var index = 0, count = 0;
            for (var i in options) {
                count++;
                var option = Y.Node.create('<option value="'+i+'">'+options[i]+'</option>');
                if (i == defaultvalue) {
                    index = count;
                }
                select.append(option);
            }
            select.set('selectedIndex', index);
        },
        populateDuration : function() {
            var select = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.DURATION+' select');
            var defaultvalue = this.get(UEP.DEFAULTDURATION);
            var index = 0, count = 0;
            for (var i = 1; i <= 365; i++) {
                count++;
                var option = Y.Node.create('<option value="'+i+'">'+M.str.enrol.durationdays.replace(/\%d/, i)+'</option>');
                if (i == defaultvalue) {
                    index = count;
                }
                select.append(option);
            }
            select.set('selectedIndex', index);
        },
        getAssignableRoles : function(){
            Y.io(M.cfg.wwwroot+'/enrol/ajax.php', {
                method:'POST',
                data:'id='+this.get(UEP.COURSEID)+'&action=getassignable&sesskey='+M.cfg.sesskey,
                on: {
                    complete: function(tid, outcome, args) {
                        try {
                            var roles = Y.JSON.parse(outcome.responseText);
                            this.set(UEP.ASSIGNABLEROLES, roles.response);
                        } catch (e) {
                            new M.core.exception(e);
                        }
                        this.getAssignableRoles = function() {
                            this.fire('assignablerolesloaded');
                        };
                        this.getAssignableRoles();
                    }
                },
                context:this
            });
        },
        preSearch : function(e) {
            this.search(null, false);
            /*
            var value = this.get(UEP.SEARCH).get('value');
            if (value.length < 3 || value == this.get(UEP.LASTSEARCH)) {
                return;
            }
            this.set(UEP.LASTSEARCH, value);
            if (this._searchTimeout) {
                clearTimeout(this._searchTimeout);
                this._searchTimeout = null;
            }
            var self = this;
            this._searchTimeout = setTimeout(function(){
                self._searchTimeout = null;
                self.search(null, false);
            }, 300);
            */
        },
        show : function(e) {
            e.preventDefault();
            e.halt();

            var base = this.get(UEP.BASE);
            base.removeClass(CSS.HIDDEN);
            var x = (base.get('winWidth') - 400)/2;
            var y = (parseInt(base.get('winHeight'))-base.get('offsetHeight'))/2 + parseInt(base.get('docScrollY'));
            if (y < parseInt(base.get('winHeight'))*0.1) {
                y = parseInt(base.get('winHeight'))*0.1;
            }
            base.setXY([x,y]);

            if (this.get(UEP.USERS)===null) {
                this.search(e, false);
            }

            this._escCloseEvent = Y.on('key', this.hide, document.body, 'down:27', this);
        },
        hide : function(e) {
            if (this._escCloseEvent) {
                this._escCloseEvent.detach();
                this._escCloseEvent = null;
            }
            this.get(UEP.BASE).addClass(CSS.HIDDEN);
            if (this.get(UEP.REQUIREREFRESH)) {
                window.location = this.get(UEP.URL);
            }
        },
        search : function(e, append) {
            if (e) {
                e.halt();
                e.preventDefault();
            }
            var on, params;
            if (append) {
                this.set(UEP.PAGE, this.get(UEP.PAGE)+1);
            } else {
                this.set(UEP.USERCOUNT, 0);
            }
            params = this.get(UEP.PARAMS);
            params['sesskey'] = M.cfg.sesskey;
            params['action'] = 'searchusers';
            params['search'] = this.get(UEP.SEARCH).get('value');
            params['page'] = this.get(UEP.PAGE);
            if (this.get(UEP.MULTIPLE)) {
                alert('oh no there are multiple');
            } else {
                var instance = this.get(UEP.INSTANCES)[0];
                params['enrolid'] = instance.id;
            }
            Y.io(M.cfg.wwwroot+this.get(UEP.AJAXURL), {
                method:'POST',
                data:build_querystring(params),
                on : {
                    start : this.displayLoading,
                    complete: this.processSearchResults,
                    end : this.removeLoading
                },
                context:this,
                arguments:{
                    append:append,
                    enrolid:params['enrolid']
                }
            });
        },
        displayLoading : function() {
            this._loadingNode.removeClass(CSS.HIDDEN);
        },
        removeLoading : function() {
            this._loadingNode.addClass(CSS.HIDDEN);
        },
        processSearchResults : function(tid, outcome, args) {
            try {
                var result = Y.JSON.parse(outcome.responseText);
                if (result.error) {
                    return new M.core.ajaxException(result);
                }
            } catch (e) {
                new M.core.exception(e);
            }
            if (!result.success) {
                this.setContent = M.str.enrol.errajaxsearch;
            }
            var users;
            if (!args.append) {
                users = Y.Node.create('<div class="'+CSS.USERS+'"></div>');
            } else {
                users = this.get(UEP.BASE).one('.'+CSS.SEARCHRESULTS+' .'+CSS.USERS);
            }
            var count = this.get(UEP.USERCOUNT);
            for (var i in result.response.users) {
                count++;
                var user = result.response.users[i];
                users.append(Y.Node.create('<div class="'+CSS.USER+' clearfix" rel="'+user.id+'"></div>')
                    .addClass((i%2)?CSS.ODD:CSS.EVEN)
                    .append(Y.Node.create('<div class="'+CSS.COUNT+'">'+count+'</div>'))
                    .append(Y.Node.create('<div class="'+CSS.PICTURE+'"></div>')
                        .append(Y.Node.create(user.picture)))
                    .append(Y.Node.create('<div class="'+CSS.DETAILS+'"></div>')
                        .append(Y.Node.create('<div class="'+CSS.FULLNAME+'">'+user.fullname+'</div>'))
                        .append(Y.Node.create('<div class="'+CSS.EMAIL+'">'+user.email+'</div>')))
                    .append(Y.Node.create('<div class="'+CSS.OPTIONS+'"></div>')
                        .append(Y.Node.create('<input type="button" class="'+CSS.ENROL+'" value="'+M.str.enrol.enrol+'" />')))
                );
            }
            this.set(UEP.USERCOUNT, count);
            if (!args.append) {
                var usersstr = (result.response.totalusers == '1')?M.str.enrol.ajaxoneuserfound:M.str.enrol.ajaxxusersfound.replace(/\[users\]/, result.response.totalusers);
                var content = Y.Node.create('<div class="'+CSS.SEARCHRESULTS+'"></div>')
                    .append(Y.Node.create('<div class="'+CSS.TOTALUSERS+'">'+usersstr+'</div>'))
                    .append(users);
                if (result.response.totalusers > (this.get(UEP.PAGE)+1)*25) {
                    var fetchmore = Y.Node.create('<div class="'+CSS.MORERESULTS+'"><a href="#">'+M.str.enrol.ajaxnext25+'</a></div>');
                    fetchmore.on('click', this.search, this, true);
                    content.append(fetchmore)
                }
                this.setContent(content);
                Y.delegate("click", this.enrolUser, users, '.'+CSS.USER+' .'+CSS.ENROL, this, args);
            } else {
                if (result.response.totalusers <= (this.get(UEP.PAGE)+1)*25) {
                    this.get(UEP.BASE).one('.'+CSS.MORERESULTS).remove();
                }
            }
        },
        enrolUser : function(e, args) {
            var user = e.currentTarget.ancestor('.'+CSS.USER);
            var params = [];
            params['id'] = this.get(UEP.COURSEID);
            params['userid'] = user.getAttribute("rel");
            params['enrolid'] = args.enrolid;
            params['sesskey'] = M.cfg.sesskey;
            params['action'] = 'enrol';
            params['role'] = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.ROLE+' select').get('value');
            params['startdate'] = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.STARTDATE+' select').get('value');
            params['duration'] = this.get(UEP.BASE).one('.'+CSS.SEARCHOPTION+'.'+CSS.DURATION+' select').get('value');
            Y.io(M.cfg.wwwroot+this.get(UEP.AJAXURL), {
                method:'POST',
                data:build_querystring(params),
                on: {
                    start : this.displayLoading,
                    complete : function(tid, outcome, args) {
                        try {
                            var result = Y.JSON.parse(outcome.responseText);
                            if (result.error) {
                                return new M.core.ajaxException(result);
                            } else {
                                args.userNode.addClass(CSS.ENROLLED);
                                args.userNode.one('.'+CSS.ENROL).remove();
                                this.set(UEP.REQUIREREFRESH, true);
                            }
                        } catch (e) {
                            new M.core.exception(e);
                        }
                    },
                    end : this.removeLoading
                },
                context:this,
                arguments:{
                    params : params,
                    userNode : user
                }
            });

        },
        setContent: function(content) {
            this.get(UEP.BASE).one('.'+CSS.CONTENT+' .'+CSS.AJAXCONTENT).setContent(content);
        }
    }, {
        NAME : UEP.NAME,
        ATTRS : {
            url : {
                validator : Y.Lang.isString
            },
            ajaxurl : {
                validator : Y.Lang.isString
            },
            base : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(UEP.NAME+': invalid base node set');
                    }
                    return n;
                }
            },
            users : {
                validator : Y.Lang.isArray,
                value : null
            },
            courseid : {
                value : null
            },
            params : {
                validator : Y.Lang.isArray,
                value : []
            },
            instances : {
                validator : Y.Lang.isArray,
                setter : function(instances) {
                    var i,ia = [], count=0;
                    for (i in instances) {
                        ia.push(instances[i]);
                        count++;
                    }
                    this.set(UEP.MULTIPLE, (count>1));
                }
            },
            multiple : {
                validator : Y.Lang.isBool,
                value : false
            },
            page : {
                validator : Y.Lang.isNumber,
                value : 0
            },
            userCount : {
                value : 0,
                validator : Y.Lang.isNumber
            },
            requiresRefresh : {
                value : false,
                validator : Y.Lang.isBool
            },
            search : {
                setter : function(node) {
                    var n = Y.one(node);
                    if (!n) {
                        Y.fail(UEP.NAME+': invalid search node set');
                    }
                    return n;
                }
            },
            lastPreSearchValue : {
                value : '',
                validator : Y.Lang.isString
            },
            strings  : {
                value : {},
                validator : Y.Lang.isObject
            },
            defaultRole : {
                value : 0
            },
            defaultStartDate : {
                value : 2,
                validator : Y.Lang.isNumber
            },
            defaultDuration : {
                value : ''
            },
            assignableRoles : {
                value : []
            },
            optionsStartDate : {
                value : []
            }
        }
    });
    Y.augment(USERENROLLER, Y.EventTarget);


    M.enrol = M.enrol || {};
    M.enrol.enrolmentmanager = {
        init : function(cfg) {
            new USERENROLLER(cfg);
        }
    }

}, '@VERSION@', {requires:['base','node', 'overlay', 'io', 'test', 'json-parse', 'event-delegate', 'dd-plugin', 'event-key', 'moodle-enrol-notification']});