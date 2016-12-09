	<nav class="top-bar" data-topbar role="navigation" style="margin-bottom: 20px;">
		<ul class="title-area">
			<li class="name">
				<h1><a href="#">EMBonds Analytics</a></h1>
			</li>
			<!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
			<li class="toggle-topbar menu-icon">
				<a href="#"><span>Menu</span></a>
			</li>
		</ul>

		<section class="top-bar-section">
			<!-- Right Nav Section -->
			<ul class="right">
				<!-- <li class="active"><a href="#">Right Button Active</a></li> -->
				<li ><a id="logfilelist" href="#">Log Files</a></li>
				<li class="has-dropdown"><a href="#">Metrics</a>
					<ul class="dropdown">
				<li ><a id="bondlist" href="#">Bonds</a></li>
				<li ><a id="userlist" href="#">Users</a></li>
				<li ><a id="cptylist" href="#">Cptys</a></li>
				<li ><a id="isincountperday" href="#">Daily ISINs</a></li>
				<li><a id="RFQs" href="rfqs.php">RFQs</a></li>
				<li><a id="Stats" href="stats.php">Stats</a></li>
				
					</ul>
				</li>
				<li ><a id="tradesummary" href="#">Trades</a></li>
				<li class="has-dropdown">
					<a href="#">Utils</a>
					<ul class="dropdown">
						<li><a id="processtradingday" href="endofday.php">End of Day</a></li>
						<li><a id="createaudio" href="sound.php">Audio Generation</a></li>
						<li><a id="excel" href="excel.php">Excel Openfin</a></li>
						<li><a id="excelv2" href="excelexample.html">Excel Example</a></li>
				<li><a id="Stats" href="bonddefs.php">Bond Defs</a></li>

					</ul>
				</li>
				<li class="has-dropdown">
					<a href="#">Graphs</a>
					<ul class="dropdown">
						<li><a id="graph_isincount" class="graphlink" href="#">ISIN Count</a></li>
						<li><a id="graph_isincount_live" class="graphlink" href="#">Live ISIN Count</a></li>
						<li><a id="graph_usercount" class="graphlink" href="#">User Count</a></li>
						
					</ul>
				</li>
			</ul>

		<!-- Left Nav Section -->
		<ul class="left">
			<li><a href="index.php">Home</a></li>
		</ul>
	</section>
	  </nav>
	