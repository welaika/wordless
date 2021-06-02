<style type="text/css">
    #error-template {
        background-color: rgba(255,255,255,0.9);
        color: #666;
        text-align: center;
        font-family: arial, sans-serif;
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: 9999;
        top: 0;
        left: 0;
    }

    #error-template > .error {
        position: relative;
        top: 50%;
        width: 25em;
        padding: 0 4em;
        margin: 0 auto;
        border: 1px solid #ccc;
        border-right-color: #999;
        border-bottom-color: #999;
    }

    h1 { font-size: 100%; color: #f00; line-height: 1.5em; }
</style>

<div id="error-template">
    <div class="error">
        <h1><?php echo isset($title) ? $title : '' ?></h1>
        <p><?php echo isset($description) ? $description : '' ?></p>
    </div>
</div>
