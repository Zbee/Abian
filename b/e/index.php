<?php
require_once("/var/www/Abian/header.php");
$bot = null;
if (isset($_GET) && count($_GET) > 0) {
  $bot = array_search(array_values($_GET)[0], $_GET);
}
if ($bot == "saved") $bot = null;
if ($session === false && $bot === null) $UserSystem->redirect301("/u/login");
if (is_array($session) && $bot !== null) {
  $bot = $UserSystem->dbSel(["bots", ["slug" => $bot]]);
  if ($bot[0] == 1) {
    $bot = $bot[1];
    $b = $bot["body"];
    $d = $bot["description"];
    $ch1 = $bot["member"] == 0 ? "checked" : "";
    $ch2 = $bot["member"] == 1 ? "checked" : "";
    $error = "";
    if (isset($_POST["n"])) {
      if ($Abian->endsWith($_POST["f"], ".zip")) {
        $slug = strtolower(
          preg_replace(
            "/\PL/u",
            "",
            preg_replace(
              "/\:[^:]+\:/",
              "",
              $_POST["n"]
            )
          )
        );
        $search = $UserSystem->dbSel(
          [
            "bots",
            [
              "slug" => $slug,
              "id" => ["!=", $bot["id"]]
            ]
          ]
        )[0];
        if ($search === 0) {
          if (file_get_contents("/var/www/Abian/dl/" . $slug . ".zip") !=
            file_get_contents($_POST["f"])) {
            $file = file_put_contents(
              "/var/www/Abian/dl/" . $slug . ".zip",
              file_get_contents($_POST["f"])
            );
          }
          $m = $_POST["m"] == 1 ? 1 : 0;
          $UserSystem->dbUpd(
            [
              "bots",
              [
                "body" => $UserSystem->sanitize($_POST["b"], "h"),
                "slug" => $slug,
                "name" => $UserSystem->sanitize($_POST["n"]),
                "description" => $UserSystem->sanitize($_POST["d"]),
                "member" => $m,
                "dateUpdate" => time()
              ],
              [
                "id" => $bot["id"]
              ]
            ]
          );
          $Abian->historify("bot.edit", $UserSystem->sanitize($_POST["n"]));
          $UserSystem->redirect301("/b?updated");
        } else {
          $error = '
            <div class="alert alert-danger">
              Name is already in use.
            </div>
          ';
        }
      } else {
        $error = '
          <div class="alert alert-danger">
            File selected is not .zip file (application/zip or
            application/x-zip-compressed).
          </div>
        ';
      }
    }
    echo <<<EOT
      <div class="col-xs-12">
        $error
        <div class="well well-md">
          <h1>Upload your bot</h1>
          <form class="form form-vertical" method="post" action="">
            <div class="form-group">
              <label for="n">Bot name</label>
              <input type="text" class="form-control" id="n" name="n"
                value="$bot[name]">
            </div>
            <div class="form-group">
              <label for="d">Description</label>
              <textarea name="d" class="form-control" rows="5">$d</textarea>
              <span id="helpBlock" class="help-block">
                This will appear with your bot in search results.
              </span>
            </div>
            <div class="form-group">
              <label for="b">Page</label>
              <textarea name="b" class="form-control" rows="15">$b</textarea>
              <span id="helpBlock" class="help-block">
                Uses
                <a href="http://s.zbee.me/bsz" target="_blank">
                  Github Flavored Markdown</a>
                and
                <a href="https://s.zbee.me/nje" target="_blank">
                  emoji</a>.
              </span>
            </div>
            <div class="form-group">
              <label for="f">File</label>
              <input type="text" class="form-control" id="f" name="f"
                value="http://abian.zbee.me/dl/$bot[slug].zip">
              <span id="helpBlock" class="help-block">
                Must be a .zip file.
              </span>
            </div>
            <div class="form-group">
              <label for="t">Tags</label>
              <input type="text" class="form-control" id="t" name="t">
              <span id="helpBlock" class="help-block">
                Separate with commas, spaces are removed.
              </span>
            </div>
            <fieldset> <!--http://s.zbee.me/fzb-->
              <div class="form-group">
                <label class="control-label">Membership required</label>
                <br>
                <div class="radio">
                  <label>
                    <input name="m" value="0" type="radio" $ch1>
                    Anyone can use
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input name="m" value="1" type="radio" $ch2>
                    Members only
                  </label>
                </div>
              </div>
            </fieldset>
            <button type="submit" class="btn btn-primary btn-block">
              Submit Bot for Reapproval
            </button>
        </form>
      </div>
    </div>
EOT;
  }
}

require_once("/var/www/Abian/footer.php");
?>
