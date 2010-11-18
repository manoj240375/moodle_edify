// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block Helper (Silvern theme)
 * @author Darryl Pogue <darryl.pogue@gmail.com>
 */
M.block_controls = {
    /**
     * Initialise the block controls system.
     */
    init: function(Y, options) {
        var BlockControls = function(options) {
            BlockControls.superclass.constructor.apply(this, arguments);
        }
        BlockControls.NAME = "BlockControls";
        BlockControls.ATTRS = {
            options: {},
            lang: {}
        };
        Y.extend(BlockControls, Y.Base, {
            initializer: function(args) {
                this.block_inst = args.blockid;
                this.userpref = args.userpref;

                //this.render();
                this.add_buttons();
            },
            destructor: function() { },
            add_buttons: function() {
                var blk = Y.one('#inst'+this.block_inst);
                var ishidden = blk.hasClass('hidden');
                var header = Y.one('#inst'+this.block_inst+' header');
                var btnclose = Y.Node.create('<img>');
                if (ishidden) {
                    btnclose.setAttribute('src', M.util.image_url('blockopen', 'theme'));
                } else {
                    btnclose.setAttribute('src', M.util.image_url('blockclose', 'theme'));
                }
                btnclose.addClass('btnclose');
                Y.on('click', this.show_hide, btnclose, this);
                header.prepend(btnclose);
            },
            show_hide: function() {
                var blck = Y.one('#inst'+this.block_inst);
                var ishidden = blck.hasClass('hidden');

                if (!ishidden) {
                    Y.one('#inst'+this.block_inst+' .btnclose').setAttribute('src', M.util.image_url('blockopen', 'theme'));
                } else {
                    Y.one('#inst'+this.block_inst+' .btnclose').setAttribute('src', M.util.image_url('blockclose', 'theme'));
                }

                ishidden = !ishidden;
                blck.toggleClass('hidden');
                M.util.set_user_preference(this.userpref, ishidden);
            }
        });

        new BlockControls(options);
    }
};
