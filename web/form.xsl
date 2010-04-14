<xsl:stylesheet version = '1.0'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method = "html" omit-xml-declaration = "yes"
	encoding="iso-8859-2" indent="yes" />

<xsl:template match="/source/form">
	<form action="index.py" method="post" name="results">
	<br />
	<center>
	<table class="index">
	<tr>
        	<th colspan="3" class="index"><div class="wlm">WEB LOG MONITOR</div></th>
	</tr>
	<tr>
        	<td colspan="3" class="small"><small>* represents all entries in the table</small></td>
	</tr>
	<tr>
		<td>
			<xsl:apply-templates select="//form/host"/> 
		</td>
		<td>
			<xsl:apply-templates select="//form/facility"/> 
		</td>
		<td>
			<xsl:apply-templates select="//form/priority"/> 
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<xsl:apply-templates select="//form/date"/> 
			<xsl:apply-templates select="//form/time"/> 
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<div class="bold">SEARCH MESSAGE:</div>
			<div class="bold">
				<input type="checkbox" name="msg1_not" id="msg1_not" value="1"/><label for="msg1_not"> NOT </label>
				<input type="text" name="msg1" size="40"/>
				<input type="radio" name="msg2_op" id="msg2_and" value="0" checked="checked"/> <label for="msg2_and"> AND </label>
				<input type="radio" name="msg2_op" id="msg2_or" value="1"/><label for="msg2_or"> OR </label>
			</div>
			<div class="bold">
				<input type="checkbox" name="msg2_not" id="msg2_not" value="1"/><label for="msg2_not"> NOT </label>
				<input type="text" name="msg2" size="40"/>
				<input type="radio" name="msg3_op" id="msg3_and" value="0" checked="checked"/><label for="msg3_and"> AND </label>
				<input type="radio" name="msg3_op" id="msg3_or" value="1"/><label for="msg3_or"> OR </label>
			</div>
			<div class="bold">
				<input type="checkbox" name="msg3_not" id="msg3_not" value="1"/><label for="msg3_not"> NOT </label>
				<input type="text" name="msg3" size="40"/>
			</div>
		</td>
	</tr>
	<tr>
        	<td colspan="3">
                	<span class="bold">RECORDS PER PAGE: </span>
	                <select class="mini" name="limit">
        	                <option>25</option>
                	        <option>50</option>
                        	<option selected="selected">100</option>
	                        <option>200</option>
        	                <option>500</option>
                	        <option>1000</option>
	                </select>

	                <span class="bold">SEARCH ORDER: </span>
	       	        <select class="mini" name="orderby">
        	                <option value = "ASC">ASC</option>
                	        <option value = "DESC" selected="selected">DESC</option>
	                </select>

			<span class="bold">FORMAT: </span>
	                <select name="format">
        	                <option value = "off">WRAP OFF</option><!-- nincs sortores -->
                	        <option value = "on">WRAP ON</option>
                        	<option value = "txt">TEXT MODE</option>
	                </select>
        	</td>
	</tr>
	<tr>
		<td colspan="3">
			<!-- Search -->
			<input type="submit" value = "{//form/submit/.}" />
			<input type="reset" value = "{//form/reset/.}" />
		</td>
	</tr>
	</table>
	</center>
	</form>
</xsl:template>

<xsl:template match="/source/form/host">
	<div class="name"><xsl:value-of select="name()"/>:</div>
	<select multiple="multiple" size="5" name="{name()}" >
	<xsl:for-each select="/source/form/host/id">
	<xsl:sort order="ascending" select="."/>
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
</xsl:template>

<xsl:template match="/source/form/facility">
	<div class="name"><xsl:value-of select="name()"/>:</div>
	<select multiple="multiple" size="5" name="{name()}" >
	<xsl:for-each select="/source/form/facility/id">
	<xsl:sort order="ascending" select="."/>
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
</xsl:template>

<xsl:template match="/source/form/priority">
	<div class="name"><xsl:value-of select="name()"/>:</div>
	<select multiple="multiple" size="5" name="{name()}" >
	<xsl:for-each select="/source/form/priority/id">
	<xsl:sort order="ascending" select="."/>
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
</xsl:template>

<xsl:template match="/source/form/date">
	<span class="name"><xsl:value-of select="name()"/>: </span>
	<select name="{name()}" >
	<xsl:for-each select="/source/form/date/id">
<!--	<xsl:sort order="descending" select="."/> -->
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
	 - 
	<select name="{name()}2" >
	<xsl:for-each select="/source/form/date/id">
<!--	<xsl:sort order="descending" select="."/> -->
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>

</xsl:template>

<xsl:template match="/source/form/time">
	<span class="name"><xsl:value-of select="name()"/>: </span>
	<select name="{name()}" >
	<xsl:for-each select="/source/form/time/id">
	<xsl:sort order="ascending" select="."/>
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
	 - 
	<select name="{name()}2" >
	<xsl:for-each select="/source/form/time/id">
	<xsl:sort order="ascending" select="."/>
		<option><xsl:value-of select="."/></option>
	</xsl:for-each>
	</select>
</xsl:template>

</xsl:stylesheet>
