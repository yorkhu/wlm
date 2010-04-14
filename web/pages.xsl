<xsl:stylesheet version = '1.0'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method = "html" omit-xml-declaration = "yes"
	encoding="iso-8859-2" indent="yes" />

<xsl:template match="/source/pages">
<div class = "pages">
Result Page:
<xsl:for-each select="/source/pages/prev/id">
	<span class="page"><xsl:value-of select="string(' ')"/> <a href = "{./link}"> <xsl:value-of select="./txt"/> </a> </span>
</xsl:for-each>

[ <span class="page"><xsl:value-of select="./now"/></span> ]

<xsl:for-each select="/source/pages/next/id">
	<span class="page"><xsl:value-of select="string(' ')"/> <a href = "{./link}"> <xsl:value-of select="./txt"/> </a> </span>
</xsl:for-each>
</div>
</xsl:template>

</xsl:stylesheet>
