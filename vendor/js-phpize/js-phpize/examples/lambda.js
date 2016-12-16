a = () => {
    return 5;
};
var b = a();
a = () => 3;

return a() + b;
