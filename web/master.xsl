<xsl:stylesheet version = '1.0'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output doctype-public = "-//W3C//DTD HTML 4.01 Transitional//HU"
	method = "html" omit-xml-declaration = "yes"
	encoding="iso-8859-2" indent="yes" />

<xsl:template match="/">
	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="/log/default.css" />
		
		<title><xsl:value-of select="/source/name" /> - <xsl:value-of select="/source/ver" /></title>
	</head>
	<body>
		<table class="header">
			<tr>
				<td class="header">
					<a href="{/source/link}"><xsl:value-of select="/source/name" /> - <xsl:value-of select="/source/ver" /></a>
				</td>
				<td class="header" align="right">
					<div><xsl:value-of select="/source/date" /></div>
					<div><xsl:value-of select="/source/ip" /></div>
				</td>
			</tr>
		</table>

	<xsl:if test="count(/source[child::form]/form) > 0">
		<xsl:apply-templates select="/source/form"/>
	</xsl:if>
		
	<xsl:if test="count(/source[child::search]/search) > 0">
		<xsl:apply-templates select="/source/search"/>
	</xsl:if>
		
	<xsl:apply-templates select="/source/pgen_time"/>
	
	<xsl:if test="count(/source[child::pages]/pages) > 0">
		<xsl:apply-templates select="/source/pages"/>
	</xsl:if>
	<xsl:if test="count(/source[child::js]/js) > 0">
		<xsl:apply-templates select="/source/js"/>
	</xsl:if>
	
	</body>
	</html>

</xsl:template>

<xsl:template match="/source/pgen_time">
	<div>Page generation time: <xsl:value-of select="/source/pgen_time" /> seconds.</div>
</xsl:template>

<xsl:include href="form.xsl"/>
<xsl:include href="search.xsl"/>
<xsl:include href="pages.xsl"/>
<xsl:include href="js.xsl"/>

</xsl:stylesheet>
