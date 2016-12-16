a = {
    b: function () {
        return 42;
    }
};
foo = 9;
bar = "9";
result = foo == bar ? a.b() : null;

return result
