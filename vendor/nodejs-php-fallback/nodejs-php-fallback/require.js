module.exports = {
    require: require,
    appendRequireMethod: function (locals) {
        locals.require = require;

        return locals;
    }
};
