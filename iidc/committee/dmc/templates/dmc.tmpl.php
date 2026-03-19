<h1 style="text-align: center; margin:15px 0">Decision Making Report</h1>
<table width="100%" style="width: 100%;" class="company_data alternate">
    <tbody>
        <tr class="firstTr">
            <td class="th" style="width: 1cm;">1</td>
            <td style="width: 200px;">Date of DMCR:</td>
            <td style="width: 11cm;"><input type="text" class="date1" name="date_of_dmcr" data-required="yes" id="DateOfDmcr"> Valid Until: [date_valid_until]<input type="hidden" name="valid_until" value="[valid_until]"></td>
        </tr>
        <tr>
            <td class="th">2</td>
            <td style="width: 200px;">Company name(s) reviewed:</td>
            <td><input type="text" name="company_name" data-required="yes" id="company_name" style="width:95%"></td>
        </tr>
        <tr>
            <th>3</th>
            <td style="width: 200px;">Client ID:</td>
            <td>[client_id]</td>
        </tr>
        <tr>
            <td class="th">4</td>
            <td style="width: 200px;">Production sites:</td>
            <td>[production_sites]</td>
        </tr>
        <tr>
            <td class="th">5</td>
            <td style="width: 200px;">Scope:</td>
            <td>[scope]</td>
        </tr>
        <tr>
            <td class="th">6</td>
            <td style="width: 200px;">Category:</td>
            <td>[category]</td>
        </tr>
        <tr>
            <td class="th">7</td>
            <td style="width: 200px;">Reference Standard:</td>
            <td>[reference_standard]</td>
        </tr>
        <tr class="lastTr">
            <td class="th">8</td>
            <td style="width: 200px;">Certificate validity:</td>
            <td>[certificate]</td>
        </tr>
    </tbody>
</table>
<h3>Section 1: Objective</h3>
<table class="table_3 alternate" style="width: 100%;">
    <tbody>
        <tr class="firstTr">
            <td><strong>Objective of the Decision-Making Committee Report</strong></td>
        </tr>
        <tr>
            <td><label><input type="radio" id="ObjectiveGrantingCertification" name="objective" value="Granting" data-required="yes" checked>Granting Certification [New Customers]</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="objective" value="Renewing or Extending" id="ObjectiveRenewingOrExtendingCertification">Renewing or Extending Certification [Existing customers]</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="objective" value="Expanding" id="ObjectiveExpandingCertification">Expanding Certification</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="objective" value="Suspending" id="ObjectiveSuspendingCertification">Suspending Certification</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="objective" value="Restoring" id="ObjectiveRestoringCertification">Restoring Certification</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="objective" value="Withdrawing" id="ObjectiveWithdrawingCertification">Withdrawing Certification</label></td>
        </tr>
        <tr class="lastTr">
            <td><label><input type="radio" name="objective" value="Others" id="ObjectiveOthers">Others: <input type="text" name="objective_others_info" id="ObjectiveOthersInfo" style="width: 80%;"></label></td>
        </tr>
    </tbody>
</table>
<div>[pdf addPage(45)]</div>
<h3>Section 2: Review</h3>
<table class="alternate" style="width:100%">
    <tbody id="trReviewed">
        <tr class="firstTr">
            <td class="th" style="width: 9cm;"><input type="checkbox" onclick="checkUncheckAll(this)" /> <strong>To be Reviewed Documents</strong></td>
            <td colspan="3" class="th" style="width: 9cm;"><strong>Results</strong></td>
        </tr>
        <tr>
            <td style="width: 9cm;"><label><input type="checkbox" name="reviewed[TyOfCe][document]" value="Type of Certificate" id="ReviewedTyOfCeDocument">Type of Certificate</label></td>
            <td style="width: 3cm;"><label><input type="radio" name="reviewed[TyofCe][results]" class="TyofCeAnnual" value="Annual" id="ReviewedTyofCeResultsAnnual">Annual</label></td>
            <td style="width: 3cm;"><label><input type="radio" name="reviewed[TyofCe][results]" value="Shipment" id="ReviewedTyofCeResultsShipment">Shipment</label></td>
            <td style="width: 3cm;"><label><input type="radio" name="reviewed[TyofCe][results]" value="Not appl" id="ReviewedTyofCeResultsNotAppl">Not appl</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[ApFoanRe][document]" value="Application Form and Review" id="ReviewedApFoanReDocument">Application Form and Review</label></td>
            <td><label><input type="radio" name="reviewed[ApFoanRe][results]" value="Accepted" id="ReviewedApFoanReResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[ApFoanRe][results]" value="In Process" id="ReviewedApFoanReResultsInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[ApFoanRe][results]" value="Refused" id="ReviewedApFoanReResultsRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[contracts][document]" value="Contracts" id="ReviewedContractsDocument">Contracts</label></td>
            <td><label><input type="radio" name="reviewed[contracts][results]" value="Accepted" id="ReviewedContractsResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[contracts][results]" value="In Process" id="ReviewedContractsResultsInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[contracts][results]" value="Refused" id="ReviewedContractsResultsRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[PrAuAc][document]" value="Pre-Audit Activities" id="ReviewedPrAuAcDocument">Pre-Audit Activities</label></td>
            <td><label><input type="radio" name="reviewed[PrAuAc][results]" value="Accepted" id="ReviewedPrAuAcResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[PrAuAc][results]" value="In Process" id="ReviewedPrAuAcResultsInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[PrAuAc][results]" value="Refused" id="ReviewedPrAuAcResultsRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[AuPl][document]" value="Audit Planning" id="ReviewedAuPlDocument">Audit Planning</label></td>
            <td><label><input type="radio" name="results[AuPl][results]" value="Accepted" id="ResultsAuPlResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="results[AuPl][results]" value="In Process" id="ResultsAuPlReInProcess">In Process</label></td>
            <td><label><input type="radio" name="results[AuPl][results]" value="Refused" id="ResultsAuPlResultsRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewd[NoCoRe][document]" value="Non-Conformance Reporting" id="ReviewdNoCoReDocument">Non-Conformance Reporting</label></td>
            <td><label><input type="radio" name="reviewd[NoCoRe][results]" value="Accepted" id="ReviewdNoCoReResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewd[NoCoRe][results]" value="In Process" id="ReviewdNoCoReResultsInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewd[NoCoRe][results]" value="Refused" id="ReviewdNoCoReResultsRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[CoAcAnEv][document]" value="Corrective Actions and Evidence" id="ReviewedCoAcAnEvDocument">Corrective Actions and Evidence</label></td>
            <td><label><input type="radio" name="reviewed[CoAcAnEv][results]" value="Accepted" id="ReviewedCoAcAnEvResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[CoAcAnEv][results]" value="In Process" id="ReviewedCoAcAnEvDocumentInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[CoAcAnEv][results]" value="Refused" id="ReviewedCoAcAnEvDocumentRefused">Refused</label></td>
        </tr>
        <tr>
            <td><label><input type="checkbox" name="reviewed[Invoicing][document]" value="Invoicing" id="ReviewedInvoicingDocument">Invoicing</label></td>
            <td><label><input type="radio" name="reviewed[Invoicing][results]" value="Accepted" id="ReviewedInvoicingResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[Invoicing][results]" value="In Process" id="ReviewedInvoicingDocumentInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[Invoicing][results]" value="Refused" id="ReviewedInvoicingDocumentRefused">Refused</label></td>
        </tr>
        <tr class="lastTr">
            <td><label><input type="checkbox" name="reviewed[PlOfNeAu][document]" value="Planning of Next Audits" id="ReviewedPlOfNeAuDocument">Planning of Next Audits</label></td>
            <td><label><input type="radio" name="reviewed[PlOfNeAu][results]" value="Accepted" id="ReviewedPlOfNeAuResultsAccepted">Accepted</label></td>
            <td><label><input type="radio" name="reviewed[PlOfNeAu][results]" value="In Process" id="ReviewedPlOfNeAuResultsInProcess">In Process</label></td>
            <td><label><input type="radio" name="reviewed[PlOfNeAu][results]" value="Refused" id="ReviewedPlOfNeAuResultsRefused">Refused</label></td>
        </tr>
    </tbody>
</table>
<h3>Section 3: Conclusion</h3>
<table style="width: 100%;" class="alternate">
    <tbody>
        <tr class="firstTr">
            <td><strong>Conclusion of the DMC Report</strong></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="conclusion" class="conclusionAgree" value="The Halal Certificate may be granted" data-required="yes" id="ConclusionTheHalalCertificateMayBeGranted">The Halal Certificate may be granted</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="conclusion" value="The Halal Certificate may be expanded [adding of more certified products]" id="ConclusionTheHalalCertificateMayBeExpandedAddingOfMoreCertifiedProducts">The Halal Certificate may be expanded [adding of more certified products]</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="conclusion" value="The Halal Certificate may be renewed or temporarily extended [max 3 months]" id="ConclusionTheHalalCertificateMayBeRenewedOrTemporarilyExtendedMax3Months">The Halal Certificate may be renewed or temporarily extended [max 3 months]</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="conclusion" value="The Halal Certificate is suspended" id="ConclusionTheHalalCertificateIsSuspended">The Halal Certificate is suspended [time period of suspension: <input type="text" name="conclution_time_period_of_suspension" id="ConclutionTimePeriodOfSuspension">]</label></td>
        </tr>
        <tr>
            <td><label><input type="radio" name="conclusion" value="The Halal Certificate shall be withdrawn" id="ConclusionTheHalalCertificateShallBeWithdrawn">The Halal Certificate shall be withdrawn from this date <input type="text" class="date" name="conclusion_withdrawn_from_this_date" id="ConclusionWithdrawnFromThisDate"></label></td>
        </tr>
        <tr class="lastTr">
            <td><label><input type="radio" name="conclusion" value="Others" id="ConclusionOthers"> Others: <input type="text" name="conclusion_other_info" id="ConclusionOtherInfo" style="width: 50%;"></label></td>
        </tr>
    </tbody>
</table>
<table class="alternate" style="width:100%">
    <tbody>
        <tr class="firstTr">
            <td><strong>Remarks on the DMC Report</strong></td>
        </tr>
        <tr class="lastTr">
            <td><textarea name="remarks_on_the_dmc_report" id="RemarksOnTheDmcReport" style="width: 100%;"></textarea></td>
        </tr>
    </tbody>
</table>
<h3>Section 4: Undersigning and Stamping by DMC Members</h3>
<div>[DDMC_members]</div>
<div style="margin-top: 20px;">Were any of the above mentioned signatories involved in the audit? *<select name="signatories_involved_in_the_audit" id="signatories_involved_in_the_audit" data-required="yes">
        <option value="">Please Select</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
    </select><br />
    (Please note that the signatories are not allowed to be involved in the audit)</div>
<div style="margin-top:20px">
    <strong>DMR Reference:</strong> <input type="text" name="dmr_reference" id="dmr_reference" placeholder="DMR Reference" value="<?php echo isset($data['dmr_reference']) ? $data['dmr_reference'] : ''; ?>" style="width:150px" />
</div>
<div style="margin-top:20px">
    <strong>Branch:</strong> [Branch]<br />
    <strong>Branch Manager:</strong> [BranchManager]<br />
    <strong>Requested By:</strong> [RequestedBy]<br /><br />
    <label><input type="checkbox" name="agree" id="agreeOnApplication" value="1" /> Hereby, I
        <strong>([RequestedBy])</strong> confirm that all the information provided in this report is accurate, and the
        committee members have reviewed, signed, and approved the content of the report</label><br /><br />
</div>