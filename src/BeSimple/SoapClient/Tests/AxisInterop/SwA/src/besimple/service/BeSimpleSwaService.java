package besimple.service;

import java.io.File;
import java.io.FileOutputStream;

import javax.xml.namespace.QName;

import javax.activation.DataHandler;
import javax.activation.FileDataSource;

import org.apache.axiom.attachments.Attachments;
import org.apache.axiom.om.OMAbstractFactory;
import org.apache.axiom.om.OMAttribute;
import org.apache.axiom.om.OMElement;
import org.apache.axiom.om.OMFactory;
import org.apache.axiom.om.OMNamespace;

import org.apache.axis2.context.MessageContext;
import org.apache.axis2.context.OperationContext;
import org.apache.axis2.wsdl.WSDLConstants;

public class BeSimpleSwaService {

    String namespace = "http://service.besimple";

    public OMElement uploadFile(OMElement element) throws Exception {
        OMElement dataElement = (OMElement)element.getFirstChildWithName(new QName(namespace, "data"));
        OMAttribute hrefAttribute = dataElement.getAttribute(new QName("href"));

        String contentID = hrefAttribute.getAttributeValue();
        contentID = contentID.trim();
        if (contentID.substring(0, 3).equalsIgnoreCase("cid")) {
            contentID = contentID.substring(4);
        }
        OMElement nameElement = (OMElement)element.getFirstChildWithName(new QName(namespace, "name"));
        String name = nameElement.getText();

        MessageContext msgCtx = MessageContext.getCurrentMessageContext();
        Attachments attachment = msgCtx.getAttachmentMap();
        DataHandler dataHandler = attachment.getDataHandler(contentID);

        File file = new File(name);
        FileOutputStream fileOutputStream = new FileOutputStream(file);
        dataHandler.writeTo(fileOutputStream);
        fileOutputStream.flush();
        fileOutputStream.close();

        OMFactory factory = OMAbstractFactory.getOMFactory();
        OMNamespace omNs = factory.createOMNamespace(namespace, "swa");
        OMElement wrapperElement = factory.createOMElement("uploadFileResponse", omNs);
        OMElement returnElement = factory.createOMElement("return", omNs, wrapperElement);
        returnElement.setText("File saved succesfully.");

        return wrapperElement;
    }

    public OMElement downloadFile(OMElement element) throws Exception {
        OMElement nameElement = (OMElement)element.getFirstChildWithName(new QName(namespace, "name"));
        String name = nameElement.getText();

        MessageContext msgCtxIn = MessageContext.getCurrentMessageContext();
        OperationContext operationContext = msgCtxIn.getOperationContext();
        MessageContext msgCtxOut = operationContext.getMessageContext(WSDLConstants.MESSAGE_LABEL_OUT_VALUE);

        FileDataSource fileDataSource = new FileDataSource(name);
        DataHandler dataHandler = new DataHandler(fileDataSource);

        String contentID = "cid:" + msgCtxOut.addAttachment(dataHandler);

        OMFactory factory = OMAbstractFactory.getOMFactory();
        OMNamespace omNs = factory.createOMNamespace(namespace, "swa");
        OMElement wrapperElement = factory.createOMElement("downloadFileResponse", omNs);
        OMElement dataElement = factory.createOMElement("data", omNs, wrapperElement);
        dataElement.addAttribute("href", contentID, null);

        return wrapperElement;
    }
}
