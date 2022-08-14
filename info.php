<html>
    <head>
        <title>Daniel's YouTube Downloader</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
    </head>
    <body>
        <div class="container">
            <a href="index.php">Back</a>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
<?php
                        $output = shell_exec('ps aux');

                        $lines = explode("\n", $output);

                        $regExpr = "^";
                        foreach($lines as $i=>$line) {
                            if($i == 0) {
                                $columns = preg_split('/( |\t)+/', $line);
                                foreach($columns as $column) {
                                    echo '                        <th>' . htmlentities($column) . "</th>\n";
                                }
                                echo "                    </tr>\n";
                                echo "                </thead>\n";
                                echo "                <tbody>\n";

                                for($i = 1; $i < count($columns); $i++) {
                                    $regExpr .= '([^ ]+) +';
                                }
                                $regExpr .= '(.*)$';
                            } else {
                                //if(strpos($line, "youtube-dl") === FALSE) {
                                //    continue;
                                //}

                                if(!preg_match('/' . $regExpr . '/', $line, $matches)) {
                                    continue;
                                }

                                array_shift($matches);

                                if (count($matches) == 0) continue;

                                if($matches[0] != 'http') {
                                    continue;
                                }

                                echo "                    <tr>\n";

                                foreach($matches as $match) {
                                    echo '                        <td>' . htmlentities($match) . "</td>\n";
                                }

                                echo "                    </tr>\n";
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
