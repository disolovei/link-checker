<?php
namespace LinkChecker;

class Template {
  public static function output() {
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Link Checker</title>
		<meta name="robots" content="noindex,nofollow">
      <style>
        ::-webkit-scrollbar {
          width: 10px;
        }

        ::-webkit-scrollbar-track {
          background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
          background: #888;
        }

        ::-webkit-scrollbar-thumb:hover {
          background: #555;
        }

/*        html {
          font-size: 0.8rem!important;
        }*/

        table {
			position: relative;
		}

        table.in-progress:after {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(33,33,33,.5);
			z-index: 1;
		}

		button:disabled {
			cursor: not-allowed;
		}
      </style>
      <link rel="stylesheet" href="assets/css/bootstrap.min.css?ver=<?php echo time(); ?>" />
    </head>
    <body>
      <div class="container pt-3">
        <h1 class="text-center mb-4">Lick checker</h1>
		  <div class="progress mb-4">
			  <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		  </div>
        <form>
          <table class="table table-bordered">
			  <thead class="thead-dark">
			  	<tr>
					<th scope="col">Acceptor('s) (|-separated for multiple links)</th>
					<th scope="col">Donor('s) (|-separated for multiple links)</th>
				</tr>
			  </thead>
			  <tbody id="rows-wrapper">
			  </tbody>
		  </table>
          <div class="d-flex">
            <button type="button" class="btn btn-primary download-report" disabled id="download-report">Download report</button>
            <button type="submit" class="btn btn-success ms-auto" id="submit">Start scan</button>
          </div>
        </form>
		  <form id="report-form">
		  </form>
      </div>
      <script type="text/html" id="row-template">
        <td>
			<input type="url" required class="form-control active acceptor" placeholder="Acceptor URL" aria-label="Acceptor URL">
			<small class="form-text text-muted">Match pattern ~^https://example.com~</small>
		</td>
		<td>
			<input type="text" required class="form-control active anchor" name="row[{{index}}][anchor]" placeholder="Donor URL" aria-label="Donor URL">
			<input readonly type="hidden" class="form-control link-found" name="row[{{index}}][found]" placeholder="rel" aria-label="rel">
			<small class="form-text text-muted">Example https://example.com|https://example1.com|https://example2.com</small>
		</td>
      </script>
	  <script type="text/html" id="report-template">
		  <input type="hidden" class="input-donor" name="report[{{index}}][donor]" value=""/>
		  <input type="hidden" class="input-found" name="report[{{index}}][found]" value=""/>
		  <input type="hidden" class="input-text" name="report[{{index}}][text]" value=""/>
		  <input type="hidden" class="input-rel" name="report[{{index}}][rel]" value=""/>
		  <input type="hidden" class="input-robots" name="report[{{index}}][robots]" value=""/>
	  </script>
      <script src="assets/js/script.js?ver=<?php echo time(); ?>" async defer></script>
    </body>
    </html>

    <?php
  }
}