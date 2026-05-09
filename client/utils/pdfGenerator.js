import { jsPDF } from "jspdf";
import html2canvas from "html2canvas";

export const generatePDF = async (elementId, filename) => {
  try {
    const element = document.getElementById(elementId);
    if (!element) {
      throw new Error("Element not found");
    }

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false,
      allowTaint: true,
    });

    const imgData = canvas.toDataURL("image/jpeg", 0.95);
    const pdf = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4",
    });

    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgWidth = pageWidth - 20;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    let heightLeft = imgHeight;
    let position = 10;

    pdf.addImage(imgData, "JPEG", 10, position, imgWidth, imgHeight);
    heightLeft -= pageHeight - 20;

    while (heightLeft >= 0) {
      position = heightLeft - imgHeight + 10;
      pdf.addPage();
      pdf.addImage(imgData, "JPEG", 10, position, imgWidth, imgHeight);
      heightLeft -= pageHeight - 20;
    }

    // Generate Blob URL and open in new tab, then save with provided filename if any
    const blobUrl = pdf.output("bloburl");
    try {
      window.open(blobUrl, "_blank");
    } catch (e) {
      // ignore popup blockers
    }
    if (filename) {
      try {
        pdf.save(filename);
      } catch (e) {
        console.error('Error saving PDF:', e);
      }
    }
  } catch (error) {
    console.error("Error generating PDF:", error);
    alert("Error generating PDF");
  }
};

export const generateQuotationPDF = (quotation, client, settings = {}) => {
  const businessName = settings.businessName || "The Patil Photography";
  const primaryLogo = settings.primaryLogo || ""; // URL or Base64
  const contactText = "Crafting beautiful moments, flawlessly documented";
  const address = settings.address || "";
  const gstNumber = settings.gstNumber || "";
  const primaryPhone = settings.primaryMobileNumber || "";
  const secondaryPhone = settings.secondaryMobileNumber || "";
  const contactEmail = settings.contactEmail || "";
  const accentColor = settings.accentColor || "#d4a574";

  const logoHtml = primaryLogo
    ? `<div style="width:50px;height:50px;background:${accentColor};border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;"><img src="${primaryLogo}" style="width:100%;height:100%;object-fit:contain;" alt="logo"/></div>`
    : `<div style="width: 50px; height: 50px; background: ${accentColor}; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><span style="color: white; font-weight: bold; font-size: 20px;">P</span></div>`;



  // Contact info HTML
  const contactInfoHtml = `
    <div style="font-size: 10px; color: #666; line-height: 1.8;">
      ${primaryPhone ? `<p style="margin: 0;">📞 ${primaryPhone}${secondaryPhone ? ` | ${secondaryPhone}` : ''}</p>` : ''}
      ${contactEmail ? `<p style="margin: 0;">✉️ ${contactEmail}</p>` : ''}
      ${address ? `<p style="margin: 0;">📍 ${address}</p>` : ''}
    </div>
  `;

  const content = `
    <div style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 40px; background: white; color: #1a1a1a;">
      <!-- Header -->
      <div style="text-align: center; margin-bottom: 40px; border-bottom: 3px solid ${accentColor}; padding-bottom: 20px;">
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 15px;">
          ${logoHtml}
          <div>
            <h1 style="margin: 0; font-size: 28px; font-weight: bold; color: #1a1a1a;">${businessName}</h1>
          </div>
        </div>
        <h2 style="font-size: 24px; font-weight: bold; margin: 20px 0 0 0; color: #1a1a1a;">QUOTATION</h2>
      </div>

      <!-- Quote Details -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
          <h3 style="color: #d4a574; font-size: 12px; font-weight: bold; margin-bottom: 10px;">QUOTATION TO</h3>
          <p style="margin: 0; font-size: 16px; font-weight: bold;">${client?.name || quotation.clientName || "Client"}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client?.email || quotation.email || ""}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client?.phone || quotation.whatsapp_no || ""}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client?.address || quotation.location || ""}</p>
        </div>
        <div style="text-align: right;">
          <p style="margin: 5px 0; font-size: 12px;"><strong>Quotation No:</strong> ${quotation.quotationNumber}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Date:</strong> ${new Date(quotation.quotationDate).toLocaleDateString()}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Event Date:</strong> ${new Date(quotation.eventDate).toLocaleDateString()}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Valid Till:</strong> ${new Date(quotation.validityDate).toLocaleDateString()}</p>
          <p style="margin: 8px 0; padding: 4px 0; font-size: 12px;"><strong>Event Type:</strong> ${quotation.eventType}</p>
        </div>
      </div>

      <!-- Services Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead>
          <tr style="background: #2d2d2d; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #d4a574;">Service</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #d4a574;">Qty</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #d4a574;">Days</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #d4a574;">Rate</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #d4a574;">Amount</th>
          </tr>
        </thead>
        <tbody>
          ${(quotation.services || [])
      .map(
        (service) => `
            <tr style="border: 1px solid #e5e5e5;">
              <td style="padding: 12px; border: 1px solid #e5e5e5;">${service.serviceName || ""}</td>
              <td style="padding: 12px; text-align: center; border: 1px solid #e5e5e5;">${service.quantity || 0}</td>
              <td style="padding: 12px; text-align: center; border: 1px solid #e5e5e5;">${service.days || 0}</td>
              <td style="padding: 12px; text-align: right; border: 1px solid #e5e5e5;">₹${(service.ratePerDay || 0).toLocaleString()}</td>
              <td style="padding: 12px; text-align: right; border: 1px solid #e5e5e5;"><strong>₹${(service.total || 0).toLocaleString()}</strong></td>
            </tr>
          `,
      )
      .join("")}
        </tbody>
      </table>

      <!-- Summary -->
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
        ${quotation.paymentTerms ? `<h3 style="color: ${accentColor}; font-weight: bold; margin-bottom: 10px; font-size: 12px;">PAYMENT TERMS</h3>
          <p style="margin: 0; font-size: 12px; line-height: 1.6;">${quotation.paymentTerms}</p>` : ''}
          ${quotation.notes ? `<h3 style="color: #d4a574; font-weight: bold; margin-top: 15px; margin-bottom: 10px; font-size: 12px;">NOTES</h3><p style="margin: 0; font-size: 12px; line-height: 1.6;">${quotation.notes}</p>` : ""}
        </div>
        <div style="background: #f5f5f5; padding: 15px; border-radius: 8px;">
          <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
            <span>Subtotal:</span>
            <span>₹${(quotation.subtotal || 0).toLocaleString()}</span>
          </div>
          ${(quotation.discount || 0) > 0
      ? `
            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
              <span>Discount ${quotation.discountType === "percentage" ? `(${quotation.discount}%)` : ""}:</span>
              <span>-₹${(quotation.discountType === "percentage" ? ((quotation.subtotal || 0) * quotation.discount) / 100 : quotation.discount).toLocaleString()}</span>
            </div>
          `
      : ""
    }
          <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
            <span>Tax (${quotation.taxPercentage || 0}%):</span>
            <span>₹${(quotation.tax || 0).toLocaleString()}</span>
          </div>
          <div style="border-top: 2px solid #d4a574; padding-top: 8px; display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; color: #d4a574;">
            <span>Grand Total:</span>
            <span>₹${(quotation.grandTotal || 0).toLocaleString()}</span>
          </div>
        </div>
      </div>

      <!-- Thank You -->
      <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;">
        <p style="margin: 0; font-size: 12px; line-height: 1.6; font-style: italic;">${quotation.thankYouMessage}</p>
      </div>

      <!-- Footer with Contact Info and Social Links -->
      <div style="border-top: 2px solid #d4a574; padding-top: 20px;">
        <div style="text-align: center; margin-bottom: 15px;">
          ${contactInfoHtml}
        </div>
        <p style="margin: 10px 0 0 0; text-align: center; font-size: 10px; color: #666;">${businessName} | ${contactText}</p>
        <p style="margin: 5px 0 0 0; text-align: center; font-size: 10px; color: #666;">This quotation is valid till ${new Date(quotation.validityDate).toLocaleDateString()}</p>
      </div>
    </div>
  `;

  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = content;
  tempDiv.id = "pdf-content";
  // FIX: html2canvas requires visibility
  tempDiv.style.position = "absolute";
  tempDiv.style.left = "-9999px";
  tempDiv.style.width = "794px"; // ~210mm @ 96dpi
  document.body.appendChild(tempDiv);

  setTimeout(async () => {
    try {
      // include client name in filename if available
      const clientName =
        client?.name || quotation.client?.name || quotation.clientName || "client";
      // sanitize client name for filename
      const clientLabel = clientName
        .trim()
        .replace(/\s+/g, "_")
        .replace(/[^a-zA-Z0-9_\-\.]/g, "")
        .slice(0, 120) || "client";
      const filename = `${clientLabel}.pdf`;
      await generatePDF("pdf-content", filename);
    } finally {
      document.body.removeChild(tempDiv);
    }
  }, 500);
};

export const generateInvoicePDF = (invoice, client, settings = {}) => {
  const businessName = settings.businessName || "The Patil Photography";
  const primaryLogo = settings.primaryLogo || "";
  const contactText = "Crafting beautiful moments, flawlessly documented";
  const address = settings.address || "";
  const gstNumber = settings.gstNumber || "";
  const primaryPhone = settings.primaryMobileNumber || "";
  const secondaryPhone = settings.secondaryMobileNumber || "";
  const contactEmail = settings.contactEmail || "";

  const logoHtml = primaryLogo
    ? `<img src="${primaryLogo}" style="height: 50px; object-fit: contain;" />`
    : `<div style="width: 50px; height: 50px; background: linear-gradient(135deg, #d4a574, #c49561); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><span style="color: white; font-weight: bold; font-size: 20px;">P</span></div>`;



  // Contact info HTML
  const contactInfoHtml = `
    <div style="font-size: 10px; color: #666; line-height: 1.8;">
      ${primaryPhone ? `<p style="margin: 0;">📞 ${primaryPhone}${secondaryPhone ? ` | ${secondaryPhone}` : ''}</p>` : ''}
      ${contactEmail ? `<p style="margin: 0;">✉️ ${contactEmail}</p>` : ''}
      ${address ? `<p style="margin: 0;">📍 ${address}</p>` : ''}
    </div>
  `;

  const content = `
    <div style="font-family: 'Playfair Display', serif; padding: 40px; background: white; color: #1a1a1a;">
      <!-- Header -->
      <div style="text-align: center; margin-bottom: 40px; border-bottom: 3px solid #d4a574; padding-bottom: 20px;">
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 15px;">
          ${logoHtml}
          <div>
            <h1 style="margin: 0; font-size: 28px; font-weight: bold; color: #1a1a1a;">${businessName}</h1>
          </div>
        </div>
        <h2 style="font-size: 24px; font-weight: bold; margin: 20px 0 0 0; color: #1a1a1a;">INVOICE</h2>
      </div>

      <!-- Invoice Details -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
          <h3 style="color: #d4a574; font-size: 12px; font-weight: bold; margin-bottom: 10px;">BILL TO</h3>
          <p style="margin: 0; font-size: 16px; font-weight: bold;">${client.name}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client.email}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client.phone}</p>
          <p style="margin: 5px 0; font-size: 12px;">${client.address || ""}</p>
        </div>
        <div style="text-align: right;">
          <p style="margin: 5px 0; font-size: 12px;"><strong>Invoice No:</strong> ${invoice.invoiceNumber}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Invoice Date:</strong> ${new Date(invoice.invoiceDate).toLocaleDateString()}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Event Date:</strong> ${new Date(invoice.eventDate).toLocaleDateString()}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Due Date:</strong> ${new Date(invoice.dueDate).toLocaleDateString()}</p>
          <p style="margin: 5px 0; font-size: 12px;"><strong>Status:</strong> <span style="color: ${invoice.paymentStatus === "Paid" ? "green" : invoice.paymentStatus === "Partially Paid" ? "orange" : "red"}; font-weight: bold;">${invoice.paymentStatus}</span></p>
        </div>
      </div>

      <!-- Services Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead>
          <tr style="background: #2d2d2d; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #d4a574;">Service</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #d4a574;">Qty</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #d4a574;">Days</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #d4a574;">Rate</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #d4a574;">Amount</th>
          </tr>
        </thead>
        <tbody>
          ${(invoice.services || [])
      .map(
        (service) => `
            <tr style="border: 1px solid #e5e5e5;">
              <td style="padding: 12px; border: 1px solid #e5e5e5;">${service.serviceName || ""}</td>
              <td style="padding: 12px; text-align: center; border: 1px solid #e5e5e5;">${service.quantity || 0}</td>
              <td style="padding: 12px; text-align: center; border: 1px solid #e5e5e5;">${service.days || 0}</td>
              <td style="padding: 12px; text-align: right; border: 1px solid #e5e5e5;">₹${(service.ratePerDay || 0).toLocaleString()}</td>
              <td style="padding: 12px; text-align: right; border: 1px solid #e5e5e5;"><strong>₹${(service.total || 0).toLocaleString()}</strong></td>
            </tr>
          `,
      )
      .join("")}
        </tbody>
      </table>

      <!-- Summary -->
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
          ${invoice.bankDetails?.accountName
      ? `
            <h3 style="color: #d4a574; font-weight: bold; margin-bottom: 10px; font-size: 12px;">PAYMENT DETAILS</h3>
            <p style="margin: 5px 0; font-size: 12px;"><strong>Account Name:</strong> ${invoice.bankDetails.accountName}</p>
            ${invoice.bankDetails.accountNumber ? `<p style="margin: 5px 0; font-size: 12px;"><strong>Account No:</strong> ${invoice.bankDetails.accountNumber}</p>` : ""}
            ${invoice.bankDetails.ifscCode ? `<p style="margin: 5px 0; font-size: 12px;"><strong>IFSC Code:</strong> ${invoice.bankDetails.ifscCode}</p>` : ""}
            ${invoice.bankDetails.upiId ? `<p style="margin: 5px 0; font-size: 12px;"><strong>UPI ID:</strong> ${invoice.bankDetails.upiId}</p>` : ""}
          `
      : ""
    }
          ${invoice.notes ? `<h3 style="color: #d4a574; font-weight: bold; margin-top: 15px; margin-bottom: 10px; font-size: 12px;">NOTES</h3><p style="margin: 0; font-size: 12px; line-height: 1.6;">${invoice.notes}</p>` : ""}
        </div>
        <div style="background: #f5f5f5; padding: 15px; border-radius: 8px;">
          <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
            <span>Subtotal:</span>
            <span>₹${(invoice.subtotal || 0).toLocaleString()}</span>
          </div>
          ${(invoice.discount || 0) > 0
      ? `
            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
              <span>Discount ${invoice.discountType === "percentage" ? `(${invoice.discount}%)` : ""}:</span>
              <span>-₹${(invoice.discountType === "percentage" ? ((invoice.subtotal || 0) * invoice.discount) / 100 : invoice.discount).toLocaleString()}</span>
            </div>
          `
      : ""
    }
          <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px;">
            <span>Tax (${invoice.taxPercentage || 0}%):</span>
            <span>₹${(invoice.tax || 0).toLocaleString()}</span>
          </div>
          <div style="border-top: 2px solid #d4a574; padding-top: 8px; display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; color: #d4a574;">
            <span>Grand Total:</span>
            <span>₹${(invoice.grandTotal || 0).toLocaleString()}</span>
          </div>
        </div>
      </div>

      <!-- Thank You -->
      <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px;">
        <p style="margin: 0; font-size: 12px; line-height: 1.6; font-style: italic;">${invoice.thankYouMessage}</p>
      </div>

      <!-- Footer with Contact Info and Social Links -->
      <div style="border-top: 2px solid #d4a574; padding-top: 20px;">
        <div style="text-align: center; margin-bottom: 15px;">
          ${contactInfoHtml}
        </div>
        <p style="margin: 10px 0 0 0; text-align: center; font-size: 10px; color: #666;">${businessName} | ${contactText}</p>
        <p style="margin: 5px 0 0 0; text-align: center; font-size: 10px; color: #666;">Invoice Date: ${new Date(invoice.invoiceDate).toLocaleDateString()}</p>
      </div>
    </div>
  `;

  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = content;
  tempDiv.id = "pdf-content";
  // FIX: html2canvas requires visibility
  tempDiv.style.position = "absolute";
  tempDiv.style.left = "-9999px";
  tempDiv.style.width = "794px"; // ~210mm @ 96dpi
  document.body.appendChild(tempDiv);

  setTimeout(async () => {
    try {
      // include client name in filename if available
      const clientName =
        client?.name || invoice.client?.name || invoice.clientName || "client";
      const clientLabel = clientName.replace(/\s+/g, "_");
      await generatePDF(
        "pdf-content",
        `Invoice-${clientLabel}-${invoice.invoiceNumber}.pdf`
      );
    } finally {
      document.body.removeChild(tempDiv);
    }
  }, 500);
};

export const generateOrderPDF = (order, settings = {}) => {
  const businessName = settings.businessName || "The Patil Photography";
  const primaryLogo = settings.primaryLogo || "";
  const contactText = "Crafting beautiful moments, flawlessly documented";
  const address = settings.address || "";
  const gstNumber = settings.gstNumber || "";
  const primaryPhone = settings.primaryMobileNumber || "";
  const secondaryPhone = settings.secondaryMobileNumber || "";
  const contactEmail = settings.contactEmail || "";

  const logoHtml = primaryLogo
    ? `<img src="${primaryLogo}" style="height: 50px; object-fit: contain;" />`
    : `<div style="width: 50px; height: 50px; background: linear-gradient(135deg, #d4a574, #c49561); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><span style="color: white; font-weight: bold; font-size: 20px;">P</span></div>`;



  // Contact info HTML
  const contactInfoHtml = `
    <div style="font-size: 10px; color: #666; line-height: 1.8;">
      ${primaryPhone ? `<p style="margin: 0;">📞 ${primaryPhone}${secondaryPhone ? ` | ${secondaryPhone}` : ''}</p>` : ''}
      ${contactEmail ? `<p style="margin: 0;">✉️ ${contactEmail}</p>` : ''}
      ${address ? `<p style="margin: 0;">📍 ${address}</p>` : ''}
    </div>
  `;

  const paid = parseFloat(order.amount_paid) || parseFloat(order.paidAmount) || 0;
  const total = parseFloat(order.amount) || 0;
  const remaining = total - paid;
  const eventDate = order.event_date || order.date ? new Date(order.event_date || order.date).toLocaleDateString() : "N/A";

  const content = `
    <div style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 50px; background: white; color: #1a1a1a;">
      <!-- Header -->
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #f0f0f0; padding-bottom: 30px;">
        <div style="display: flex; gap: 20px; align-items: center;">
          ${logoHtml}
          <div>
            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #111; letter-spacing: -0.5px;">${businessName}</h1>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">${contactText}</p>
          </div>
        </div>
        <div style="text-align: right;">
          <h2 style="font-size: 28px; font-weight: 800; margin: 0; color: #d4a574; letter-spacing: 1px; text-transform: uppercase;">RECEIPT</h2>
          <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;"><strong>ID:</strong> #${order._id.slice(-6).toUpperCase()}</p>
          <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;"><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
        </div>
      </div>

      <!-- Receipt Details -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; background: #fafafa; padding: 25px; border-radius: 12px;">
        <div>
          <h3 style="color: #888; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Billed To</h3>
          <p style="margin: 0; font-size: 18px; font-weight: 700; color: #111;">${order.name || order.customerName}</p>
          ${(order.email) ? `<p style="margin: 6px 0 0 0; font-size: 13px; color: #555;">${order.email}</p>` : ''}
          ${(order.whatsapp_no || order.customerPhone) ? `<p style="margin: 4px 0 0 0; font-size: 13px; color: #555;">${order.whatsapp_no || order.customerPhone}</p>` : ''}
        </div>
        <div>
          <h3 style="color: #888; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Event Details</h3>
          <p style="margin: 0; font-size: 15px; font-weight: 600; color: #333;">${order.event_name || "-"}</p>
          <p style="margin: 6px 0 0 0; font-size: 13px; color: #555;"><strong>Date:</strong> ${eventDate}</p>
          ${(order.photography_type) ? `<p style="margin: 4px 0 0 0; font-size: 13px; color: #555;"><strong>Type:</strong> ${order.photography_type}</p>` : ''}
        </div>
      </div>

      <!-- Financial Summary Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
        <thead>
          <tr style="border-bottom: 2px solid #111;">
            <th style="padding: 16px 12px; text-align: left; font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px;">Description</th>
            <th style="padding: 16px 12px; text-align: right; font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 20px 12px; font-size: 15px; color: #333; font-weight: 500;">Total Project Value ${(order.photography_type) ? `(${order.photography_type})` : ''}</td>
            <td style="padding: 20px 12px; text-align: right; font-size: 15px; color: #111; font-weight: 600;">₹${total.toLocaleString()}</td>
          </tr>
        </tbody>
      </table>

      <!-- Summary / Balance -->
      <div style="display: flex; justify-content: flex-end; margin-bottom: 40px;">
        <div style="width: 320px;">
          <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 12px; color: #555;">
            <span>Total Amount:</span>
            <span style="font-weight: 600; color: #111;">₹${total.toLocaleString()}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 16px; color: #555;">
            <span>Amount Received:</span>
            <span style="font-weight: 600; color: #10b981;">- ₹${paid.toLocaleString()}</span>
          </div>
          <div style="border-top: 2px solid #111; padding-top: 16px; display: flex; justify-content: space-between; font-size: 18px;">
            <span style="font-weight: 700; color: #111;">Balance Due:</span>
            <span style="font-weight: 800; color: ${remaining > 0 ? '#ef4444' : '#10b981'};">
              ₹${remaining > 0 ? remaining.toLocaleString() : "0.00"}
            </span>
          </div>
        </div>
      </div>

      <!-- Thank You -->
      <div style="background: ${remaining <= 0 ? '#ecfdf5' : '#fef2f2'}; border: 1px solid ${remaining <= 0 ? '#10b981' : '#ef4444'}; padding: 24px; border-radius: 12px; text-align: center; margin-bottom: 40px;">
        <p style="margin: 0; font-size: 15px; font-weight: 600; color: ${remaining <= 0 ? '#047857' : '#b91c1c'};">
          ${remaining <= 0 ? '✨ Thank you! This order is fully paid.' : '🔔 A balance payment is pending for this order.'}
        </p>
      </div>

      <!-- Footer -->
      <div style="border-top: 1px solid #eee; padding-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div style="font-size: 11px; color: #888; line-height: 1.8;">
            ${primaryPhone ? `<span style="margin-right: 15px;">📞 ${primaryPhone}${secondaryPhone ? ` | ${secondaryPhone}` : ''}</span>` : ''}
            ${contactEmail ? `<span style="margin-right: 15px;">✉️ ${contactEmail}</span>` : ''}
            ${address ? `<span>📍 ${address}</span>` : ''}
          </div>
          <div style="font-size: 11px; color: #aaa; font-style: italic;">
            Generated on ${new Date().toLocaleDateString()}
          </div>
        </div>
      </div>
    </div>
  `;

  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = content;
  tempDiv.id = "pdf-content";
  tempDiv.style.position = "absolute";
  tempDiv.style.left = "-9999px";
  tempDiv.style.width = "794px";
  document.body.appendChild(tempDiv);

  setTimeout(async () => {
    try {
      const clientName = order.name || order.customerName || "Order";
      await generatePDF("pdf-content", `${clientName.replace(/\s+/g, '_')}_Payment_Receipt.pdf`);
    } finally {
      document.body.removeChild(tempDiv);
    }
  }, 500);
};
