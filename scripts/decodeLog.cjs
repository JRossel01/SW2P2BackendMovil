require("dotenv").config();
const { ethers } = require("ethers");
const axios = require("axios");

const ABI = [
  "event PdfHashRegistered(uint indexed patientId, string pdfHash, uint timestamp)"
];
const iface = new ethers.Interface(ABI);

const CONTRACT_ADDRESS = "0x260B76B3557A846cbF0313Bb525880427FfF5833";
const POLYGONSCAN_API_KEY = process.env.POLYGONSCAN_API_KEY;
const POLYGONSCAN_URL = "https://api-amoy.polygonscan.com/api";

async function main() {
  const [, , inputHash] = process.argv;
  if (!inputHash) {
    console.error("Uso: node decodeLogs.cjs <pdfHash>");
    process.exit(1);
  }

  const response = await axios.get(POLYGONSCAN_URL, {
    params: {
      module: "logs",
      action: "getLogs",
      fromBlock: "0",
      toBlock: "latest",
      address: CONTRACT_ADDRESS,
      apikey: POLYGONSCAN_API_KEY,
    },
  });

  const logs = response.data.result;
  for (const log of logs) {
    try {
      const parsed = iface.parseLog(log);
      const pdfHash = parsed.args.pdfHash;
      if (pdfHash.toLowerCase() === inputHash.toLowerCase()) {
        console.log(JSON.stringify({
          found: true,
          patientId: parsed.args.patientId.toString(),
          timestamp: parsed.args.timestamp.toString(),
          transactionHash: log.transactionHash
        }));
        return;
      }
    } catch (err) {
      // No es el evento esperado, continuar
    }
  }

  console.log(JSON.stringify({ found: false }));
}

main();
