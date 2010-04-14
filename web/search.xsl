<xsl:stylesheet version = '1.0'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method = "html" omit-xml-declaration = "yes"
	encoding="iso-8859-2" indent="yes" />

<xsl:template match="/source/search">
	<table border="0" cellpadding="0" cellspacing="0" bordercolor="#111111" width="100%" height="86">
	<tr>
		<td colspan="3"><h2>RESULTS</h2></td>
	</tr>
	<tr>
		<td align="left">
			<a href="{//back/link}"><xsl:value-of select="//back/txt"/></a><br/>
			<span class="italic"><xsl:value-of select="//rows/txt"/></span><span class="bold"><xsl:value-of select="//rows/num"/></span>
		</td>
		<td align="right">
			<div class="bold">SEVERITY LEGEND</div>
			<span class="priority_info">INFO</span>
			<span class="priority_debug">DEBUG</span>
			<span class="priority_notice">NOTICE</span>
			<span class="priority_warning">WARNING</span>
			<span class="priority_err">ERR</span>
			<span class="priority_crit">CRIT</span>
			<span class="priority_alert">ALERT</span>
		</td>
	</tr>
	</table>
<xsl:choose >
<xsl:when test="not(//wrap/. = string('txt'))">
	<table class="results">
	<tr>
		<th>SEQ</th>
		<th>HOST</th>
		<th>PRIORITY</th>

		<th>DATE</th>
		<th>FACILITY</th>
		<th width="100%">MESSAGE</th>
	</tr>
	<xsl:for-each select="/source/search/id">
		<tr class="{./class}">
			<td nowrap="nowrap"><xsl:value-of select="./seq"/></td>
			<td nowrap="nowrap"><a href="{./host_link}" class="td"><xsl:value-of select="./host"/></a></td>
			<td class="{./priority_class}" nowrap="nowrap"><a href="{./priority_link}" class="td"><xsl:value-of select="./priority"/></a></td>
			<td nowrap="nowrap"><xsl:value-of select="./date"/></td>
			<td nowrap="nowrap"><a href="{./facility_link}" class="td"><xsl:value-of select="./facility"/></a></td>
			<td class = "{//wrap}"><xsl:value-of select="./msg"/></td>
		</tr>
	</xsl:for-each>
	</table>
</xsl:when>

<xsl:otherwise>
	<xsl:for-each select="/source/search/id">
	<div>
	<span><xsl:value-of select="./date"/><xsl:value-of select="string(' ')"/></span>
	
	<span><a class = "td" href="{./host_link}"><xsl:value-of select="./host"/></a></span>
	/
	<span><a class = "td" href="{./facility_link}"><xsl:value-of select="./facility"/></a><xsl:value-of select="string(' ')"/></span>
	
	<span><xsl:value-of select="./msg"/></span>
	</div>
	</xsl:for-each>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

</xsl:stylesheet>
