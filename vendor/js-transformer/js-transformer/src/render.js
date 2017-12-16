var packageName = process.argv[2];
var argumentsList = require(process.argv[3]);

var transformer = require('jstransformer')(require(packageName));

transformer.renderAsync.apply(transformer, argumentsList).then(function (result) {
    console.log(result.body);
});
