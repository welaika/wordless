foo = { bar: { "baz": "hello" } };
// Comment
biz = 'bar'; // com

return foo.bar["baz"] + ' ' + foo[biz].baz + " " + foo.bar.baz;
